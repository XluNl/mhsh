<?php


namespace business\services;


use business\models\BusinessCommon;
use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\DeliverySelect;
use common\models\SystemOptions;
use common\models\WechatPayLog;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use Yii;
use yii\db\Query;

class DeliveryService extends \common\services\DeliveryService
{

    public static function generateAuthOrderNo($deliveryId, $time){
        $str = "DAUTH";
        $timeStr = date("YmdH",$time);
        return $str.StringUtils::fullZeroForNumber($deliveryId,9).$timeStr;
    }

    public static function generateChargeOrderNo($deliveryId,$time){
        $str = "DCHAR";
        $timeStr = date("YmdH",$time);
        return $str.StringUtils::fullZeroForNumber($deliveryId,9).$timeStr;
    }

    public static function requiredModel($id, $model = false){
        $deliveryModel = parent::getActiveModel($id,null,$model);
        ExceptionAssert::assertNotNull($deliveryModel,StatusCode::createExpWithParams(StatusCode::DELIVERY_NOT_EXIST,'配送点不存在'));
        return $deliveryModel;
    }

    /**
     * 获取当前设定的配送点
     * @param $userId
     * @return |null
     */
    public static function getSelectedDeliveryId($userId){
        $deliverySelectTable = DeliverySelect::tableName();
        $deliveryTable = Delivery::tableName();
        $deliverySelect = (new Query())->from($deliverySelectTable)
            ->innerJoin($deliveryTable,"{$deliveryTable}.id={$deliverySelectTable}.delivery_id")
            ->where([
                "{$deliverySelectTable}.user_id"=>$userId,
                "{$deliveryTable}.status"=>CommonStatus::STATUS_ACTIVE,
            ])->one();
        if (empty($deliverySelect)){
            $deliveryModels = parent::getActiveModelByUserId($userId);
            if (empty($deliveryModels)){
                return null;
            }
            return $deliveryModels[0]['id'];
        }
        return $deliverySelect['delivery_id'];
    }

    /**
     * 修改默认配送点
     * @param $userId
     * @param $deliveryId
     */
    public static function changeSelectedDeliveryId($userId,$deliveryId){
        $deliveryModel = self::requiredModel($deliveryId);
        ExceptionAssert::assertTrue($deliveryModel['user_id']==$userId,StatusCode::createExpWithParams(StatusCode::DELIVERY_CHANGE_ERROR,'配送点不属于你'));
        $deliverySelect = DeliverySelect::find()->where(['user_id'=>$userId])->one();
        if (empty($deliverySelect)){
            $deliverySelect = new DeliverySelect();
            $deliverySelect->user_id = $userId;
        }
        $deliverySelect->delivery_id = $deliveryId;
        ExceptionAssert::assertTrue($deliverySelect->save(),StatusCode::createExpWithParams(StatusCode::DELIVERY_CHANGE_ERROR,'保存记录失败'));
    }

    /**
     * 获取列表（带当前选择的团长信息）
     * @param $userId
     * @return array|bool|Delivery|\yii\db\ActiveRecord|null
     */
    public static function getListWithDefault($userId)
    {
        $list = parent::getActiveModelByUserId($userId);
        if (!empty($list)){
            $selectId = self::getSelectedDeliveryId($userId);
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
     * @param $deliveryModel
     * @return array
     */
    public static function auth($user, $deliveryModel){
        ExceptionAssert::assertTrue($deliveryModel['auth']!=Delivery::AUTH_STATUS_AUTH,StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_ERROR,'已认证'));
        $authMoney = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_ALLIANCE_AUTH_MONEY);
        $jsSdkPayInfo = self::generateJsSdkPayInfo($user['openid'],$deliveryModel['id'],$authMoney);
        $config = [];
        $config['money'] = $authMoney;
        $config['jsSdkPayInfo'] = $jsSdkPayInfo;
        return $config;
    }


    public static function payCallBack($data,&$fail){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $deliveryId = $data['attach'];
            //判断是否已经处理过
            $exOrderPayCnt = WechatPayLog::find()->where(['transaction_id'=>$data['transaction_id']])->asArray()->count();
            if ($exOrderPayCnt>0){
                $transaction->rollBack();
                return true;
            }
            $orderQueryResult = Yii::$app->businessWechat->payment->order->queryByOutTradeNumber($data['out_trade_no']);
            ExceptionAssert::assertTrue($orderQueryResult['return_code'] === 'SUCCESS',StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_PAY_CALLBACK_ERROR,'通信失败，请稍后再通知我'));
            if ($orderQueryResult['trade_state'] !== 'SUCCESS'){
                $transaction->rollBack();
                return true;
            }
            $delivery = Delivery::findOne(['id'=>$deliveryId]);
            ExceptionAssert::assertNotNull($delivery,StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_PAY_CALLBACK_ERROR,"社区合伙人不存在"));
            $authMoney = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_DELIVERY_AUTH_MONEY);
            ExceptionAssert::assertTrue($authMoney*100==$data["total_fee"],StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_PAY_CALLBACK_ERROR,'运营服务费金额与实际支付金额不一致'));
            $payLog = new WechatPayLog();
            $payLog->company_id = !empty($order)?$delivery->company_id:WechatPayLog::$UN_KNOWN_COMPANY;
            $payLog->biz_type = WechatPayLog::BIZ_TYPE_DELIVERY_AUTH;
            $payLog->biz_id = $deliveryId;
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
            ExceptionAssert::assertTrue($payLog->save(),StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_PAY_CALLBACK_ERROR,'回调数据保存失败'));
            $delivery->auth= Delivery::AUTH_STATUS_AUTH;
            $delivery->auth_id = $payLog->id;
            ExceptionAssert::assertTrue($delivery->save(),StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_PAY_CALLBACK_ERROR,'认证状态更新失败'));
            $transaction->commit();
            return true;
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            $fail($e->getMessage());
        }
    }




    private static function generateJsSdkPayInfo($openid, $deliveryId, $authMoney){
        $result = Yii::$app->businessWechat->payment->order->unify([
            'body' => "满好生活-社区合伙人运营服务费({$deliveryId})",
            'out_trade_no' => DeliveryService::generateAuthOrderNo($deliveryId,time()),
            'attach'=>$deliveryId,
            'total_fee' => $authMoney*100,
            'notify_url' => BusinessCommon::getAuthCallBackUrl(), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openid,
        ]);
        ExceptionAssert::assertNotEmpty($result, StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_ERROR, '微信支付创建失败'));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('return_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_ERROR,ArrayUtils::getArrayValue('return_msg',$result,"")));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('result_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_ERROR,ArrayUtils::getArrayValue('err_code_des',$result,"")));
        return Yii::$app->businessWechat->payment->jssdk->bridgeConfig($result['prepay_id'],false);
    }
}