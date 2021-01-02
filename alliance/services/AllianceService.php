<?php


namespace alliance\services;

use alliance\models\AllianceCommon;
use alliance\utils\ExceptionAssert;
use alliance\utils\StatusCode;
use common\models\Alliance;
use common\models\AllianceSelect;
use common\models\CommonStatus;
use common\models\SystemOptions;
use common\models\WechatPayLog;
use common\services\SystemOptionsService;
use common\utils\ArrayUtils;
use Yii;
use yii\db\Query;

class AllianceService extends \common\services\AllianceService
{

    public static function requiredModel($id, $model = false){
        $allianceModel = parent::getActiveModel($id,null,$model);
        ExceptionAssert::assertNotNull($allianceModel,StatusCode::createExpWithParams(StatusCode::ALLIANCE_NOT_EXIST,'配送点不存在'));
        return $allianceModel;
    }

    /**
     * 排除不允许修改字段
     * @param $model Alliance
     */
    public static function checkCanModifyAttr($model){
        $dirtyAttrs = $model->getDirtyAttributes();
        $dirtyAttrKeys = array_keys($dirtyAttrs);
        $canModifyKeys = [
            'nickname','realname','em_phone','wx_number','occupation','province_id','city_id','county_id',
            'community','address','lat','lng','store_images','qualification_images','business_start','business_end'];
        $diffKeys = array_diff($dirtyAttrKeys,$canModifyKeys);
        ExceptionAssert::assertEmpty($diffKeys,StatusCode::createExpWithParams(StatusCode::ALLIANCE_MODIFY_ERROR,implode(",", $diffKeys).'不允许修改'));
    }

    /**
     * 获取当前设定的配送点
     * @param $userId
     * @return |null
     */
    public static function getSelectedId($userId){
        $allianceSelectTable = AllianceSelect::tableName();
        $allianceTable = Alliance::tableName();
        $allianceSelect = (new Query())->from($allianceSelectTable)
            ->innerJoin($allianceTable,"{$allianceTable}.id={$allianceSelectTable}.alliance_id")
            ->where([
                "{$allianceSelectTable}.user_id"=>$userId,
                "{$allianceTable}.status"=>CommonStatus::STATUS_ACTIVE,
            ])->one();
        if (empty($allianceSelect)){
            $allianceModels = parent::getActiveModelByUserId($userId);
            if (empty($allianceModels)){
                return null;
            }
            return $allianceModels[0]['id'];
        }
        return $allianceSelect['alliance_id'];
    }

    /**
     * 修改默认配送点
     * @param $userId
     * @param $allianceId
     */
    public static function changeSelectedId($userId, $allianceId){
        $allianceModel = self::requiredModel($allianceId);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$userId,StatusCode::createExpWithParams(StatusCode::ALLIANCE_CHANGE_ERROR,'联盟点不属于你'));
        $allianceSelect = AllianceSelect::find()->where(['user_id'=>$userId])->one();
        if (empty($allianceSelect)){
            $allianceSelect = new AllianceSelect();
            $allianceSelect->user_id = $userId;
        }
        $allianceSelect->alliance_id = $allianceId;
        ExceptionAssert::assertTrue($allianceSelect->save(),StatusCode::createExpWithParams(StatusCode::ALLIANCE_CHANGE_ERROR,'保存记录失败'));
    }

    /**
     * 获取列表（带当前选择的联盟点信息）
     * @param $userId
     * @return array|bool|Alliance|\yii\db\ActiveRecord|null
     */
    public static function getListWithDefault($userId)
    {
        $list = parent::getActiveModelByUserId($userId);
        if (!empty($list)){
            $selectId = self::getSelectedId($userId);
            foreach ($list as $k=>$v){
                if ($v['id']==$selectId){
                    $v['selected'] = true;
                }
                else{
                    $v['selected'] = false;
                }
                $list[$k] = $v;
            }
        }
        return $list;
    }


    /**
     * 认证信息和支付单
     * @param $user
     * @param $allianceModel
     * @return array
     */
    public static function auth($user,$allianceModel){
        ExceptionAssert::assertTrue($allianceModel['auth']!=Alliance::AUTH_STATUS_AUTH,StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_ERROR,'已认证'));
        $authMoney = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_ALLIANCE_AUTH_MONEY);
        $jsSdkPayInfo = self::generateJsSdkPayInfo($user['openid'],$allianceModel['id'],$authMoney);
        $config = [];
        $config['money'] = $authMoney;
        $config['jsSdkPayInfo'] = $jsSdkPayInfo;
        return $config;
    }


    private static function generateJsSdkPayInfo($openid,$allianceId,$authMoney){
        $result = Yii::$app->allianceWechat->payment->order->unify([
            'body' => "满好生活-店铺保证金({$allianceId})",
            'out_trade_no' => AllianceService::generateAuthOrderNo($allianceId,time()),
            'attach'=>$allianceId,
            'total_fee' => $authMoney*100,
            'notify_url' => AllianceCommon::getAuthCallBackUrl(), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openid,
        ]);
        ExceptionAssert::assertNotEmpty($result, StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_ERROR, '微信支付创建失败'));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('return_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_ERROR,ArrayUtils::getArrayValue('return_msg',$result,"")));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('result_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_ERROR,ArrayUtils::getArrayValue('err_code_des',$result,"")));
        $jsSdkConfig = Yii::$app->allianceWechat->payment->jssdk->bridgeConfig($result['prepay_id'],false);
        return $jsSdkConfig;
    }


    public static function payCallBack($data,&$fail){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $allianceId = $data['attach'];
            //判断是否已经处理过
            $exOrderPayCnt = WechatPayLog::find()->where(['transaction_id'=>$data['transaction_id']])->asArray()->count();
            if ($exOrderPayCnt>0){
                $transaction->rollBack();
                return true;
            }
            $orderQueryResult = Yii::$app->allianceWechat->payment->order->queryByOutTradeNumber($data['out_trade_no']);
            ExceptionAssert::assertTrue($orderQueryResult['return_code'] === 'SUCCESS',StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_PAY_CALLBACK_ERROR,'通信失败，请稍后再通知我'));
            if ($orderQueryResult['trade_state'] !== 'SUCCESS'){
                $transaction->rollBack();
                return true;
            }
            $alliance = Alliance::findOne(['id'=>$allianceId]);
            ExceptionAssert::assertNotNull($alliance,StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_PAY_CALLBACK_ERROR,"联盟点不存在"));
            $authMoney = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_ALLIANCE_AUTH_MONEY);
            ExceptionAssert::assertTrue($authMoney*100==$data["total_fee"],StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_PAY_CALLBACK_ERROR,'保证金金额与实际支付金额不一致'));
            $payLog = new WechatPayLog();
            $payLog->company_id = !empty($order)?$alliance->company_id:WechatPayLog::$UN_KNOWN_COMPANY;
            $payLog->biz_type = WechatPayLog::BIZ_TYPE_ALLIANCE_AUTH;
            $payLog->biz_id = $allianceId;
            $payLog->out_trade_no = $data["out_trade_no"];
            $payLog->transaction_id = $data["transaction_id"];
            $payLog->attach = $data["attach"];
            $payLog->total_fee = $data["total_fee"];
            $payLog->remain_fee = $data["total_fee"];
            $payLog->settlement_total_fee = ArrayUtils::getArrayValue('settlement_total_fee',$data,'');
            $payLog->bank_type = ArrayUtils::getArrayValue($data["bank_type"], Yii::$app->params['bank_type'],$data["bank_type"]);
            $payLog->openid = $data["openid"];
            $payLog->nonce_str = $data["nonce_str"];
            $payLog->time_end = $data["time_end"];
            $payLog->sign = $data["sign"];
            $payLog->trade_type = $data["trade_type"];
            ExceptionAssert::assertTrue($payLog->save(),StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_PAY_CALLBACK_ERROR,'回调数据保存失败'));
            $alliance->auth= Alliance::AUTH_STATUS_AUTH;
            $alliance->auth_id = $payLog->id;
            ExceptionAssert::assertTrue($alliance->save(),StatusCode::createExpWithParams(StatusCode::ALLIANCE_AUTH_PAY_CALLBACK_ERROR,'门店认证状态更新失败'));
            $transaction->commit();
            return true;
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            $fail($e->getMessage());
        }
    }

}