<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:21
 */

namespace frontend\services;


use common\models\Common;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\RoleEnum;
use common\models\UserInfo;
use common\services\AdminUserService;
use common\services\CustomerService;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use frontend\components\GlobalLog;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CouponBatchService extends \common\services\CouponBatchService
{
    public static function drawPublicCoupon($company_id,$customerId,$batchNo,$num,$operatorId,$operatorName){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = parent::drawCoupon($company_id,$customerId,$batchNo,$num,true,$operatorId,$operatorName,RoleEnum::ROLE_CUSTOMER);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::DRAW_COUPON_ERROR,$error));
            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            \yii::error($e->getMessage());
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::DRAW_COUPON_ERROR,$e->getMessage()));
        }
    }

    public static function getAvailableSkuCouponList($company_id, $skuIds){
        $nowTime = DateTimeUtils::parseStandardWLongDate(time());
        $conditions = [
            'and',
            [
                'company_id'=>$company_id,
                'is_public'=>CouponBatch::PUBLIC_TRUE,
                'use_limit_type'=>Coupon::LIMIT_TYPE_GOODS_SKU,
                'use_limit_type_params'=>$skuIds,
                'status'=>CouponBatch::STATUS_ACTIVE,
                'coupon_type' =>CouponBatch::COUPON_PLAN
            ],
            ['<=','draw_start_time',$nowTime],
            ['>=','draw_end_time',$nowTime],
        ];
        $couponBatchArr = (new Query())->from(CouponBatch::tableName())->where($conditions)->all();
        $couponBatchArr = CouponDisplayDomainService::batchDefineDescVO($couponBatchArr); 
        $couponBatchArr = self::removeUnusedMsg($couponBatchArr);       
        $couponBatchArr =  self:: batchUseTimeDesc($couponBatchArr);
        return $couponBatchArr;
    }

    public static function batchUseTimeDesc($batchs){
        if (empty($batchs)){
            return [];
        }
        foreach ($batchs as $k => $v) {
            $use_time_feature    = json_decode($v['use_time_feature']);
            $user_time_type_stat = $use_time_feature->start_time??'';
            $user_time_type_end  = $use_time_feature->end_time??'';
            $user_time_days      = $use_time_feature->days??0;
            if($v['user_time_type']==CouponBatch::USER_TIME_FEATURE_RANG){
                $v['use_start_time'] = $user_time_type_stat;
                $v['use_end_time'] = $user_time_type_end;
            }
            if($v['user_time_type']==CouponBatch::USER_TIME_FEATURE_CUR){
                $v['use_start_time'] = '领取当日起'.$user_time_days.'日内';
                $v['use_end_time'] = '领取当日起'.$user_time_days.'日内';
            }
            if($v['user_time_type']==CouponBatch::USER_TIME_FEATURE_NEXT){
                $v['use_start_time'] = '领取次日起'.$user_time_days.'日内';
                $v['use_end_time'] = '领取次日起'.$user_time_days.'日内';
            }
            $batchs[$k] = $v;
        }
      return $batchs;
    }


    /**
     * 获取可领取的优惠券（除sku级别优惠券）
     * @param $company_id
     * @param $customerModel
     * @param $couponType
     * @return array
     */
    public static function getAvailableCouponList($company_id,$customerModel,$couponType){
        $nowTime = DateTimeUtils::parseStandardWLongDate(time());
        $conditions = [
            'and',
            [
                'company_id'=>$company_id,
                'is_public'=>CouponBatch::PUBLIC_TRUE,
                'status'=>CouponBatch::STATUS_ACTIVE,
                'coupon_type' =>CouponBatch::COUPON_PLAN
            ],
            ['<=','draw_start_time',$nowTime],
            ['>=','draw_end_time',$nowTime],
        ];
        if (StringUtils::isNotEmpty($couponType)){
            $conditions[] = ['use_limit_type'=>$couponType];
        }
        $couponBatchArr = (new Query())->from(CouponBatch::tableName())->where($conditions)->all();
        $couponBatchArr = self::completeDrawStatus($couponBatchArr,$company_id,$customerModel);
        //$couponBatchArr = self::completeCouponIcon($couponBatchArr,$couponType,$company_id);
        $couponBatchArr = self::batchCompleteCouponIcon($couponBatchArr,$company_id);
        $couponBatchArr = CouponDisplayDomainService::batchDefineDescVO($couponBatchArr);
        $couponBatchArr = self::removeUnusedMsg($couponBatchArr);
        $couponBatchArr = self:: batchUseTimeDesc($couponBatchArr);
        return $couponBatchArr;
    }

    /**
     * 补全优惠券信息&返回优惠券列表
     * @param $company_id
     * @param $skuModels
     * @param $couponBatchList
     */
    public static function assembleAvailableCouponListMultipleGoods($company_id, &$skuModels,&$couponBatchList){
        if (empty($skuModels)){
            return;
        }
        $skuIds = ArrayHelper::getColumn($skuModels,'sku_id');
        $couponBatchArr = self::getAvailableSkuCouponList($company_id,$skuIds);
        if (!empty($couponBatchArr)){
            foreach ($couponBatchArr as $ck=>$cv){
                $skuId = $cv['use_limit_type_params'];
                foreach ($skuModels as $sk=>$sv){
                    if ($sv['sku_id']==$skuId){
                        if (!key_exists('coupon_batches',$skuModels[$sk])){
                            $skuModels[$sk]['coupon_batches'] = [];
                        }
                        $skuModels[$sk]['coupon_batches'][] = $cv;
                        //商品数据加入到优惠券里
                        $coupon = $cv;
                        $coupon['sku']=$sv;
                        $couponBatchList[] = $coupon;
                    }
                }
            }
        }
        $skuModels = array_values($skuModels);
    }

    /**
     * 去除无关字段
     * @param $couponBatchList
     * @return array
     */
    private static function removeUnusedMsg($couponBatchList){
        if (empty($couponBatchList)){
            return [];
        }
        foreach ($couponBatchList as $k=>$v){
            unset($v['created_at']);
            unset($v['updated_at']);
            unset($v['draw_customer_type']);
            unset($v['draw_customer_phones']);
            unset($v['operator_id']);
            unset($v['operator_name']);
            unset($v['version']);
            unset($v['is_public']);
            unset($v['restore']);
            unset($v['company_id']);
            unset($v['draw_start_time']);
            unset($v['draw_end_time']);
            unset($v['draw_limit_type']);
            unset($v['draw_limit_type_params']);
            $couponBatchList[$k] = $v;
        }
        return $couponBatchList;
    }

    /**
     * 补全商品图片信息
     * @param $couponBatchList
     * @param $companyId
     * @return array
     */
    private static function batchCompleteCouponIcon($couponBatchList,$companyId){
        if (empty($couponBatchList)){
            return [];
        }
        $skuModelArr = [];
        $skuIds = [];
        foreach ($couponBatchList as $k=>$v){
            if ($v['use_limit_type']==Coupon::LIMIT_TYPE_GOODS_SKU){
                $skuIds[] = $v['use_limit_type_params'];
            }
        }
        if (!empty($skuIds)){
            $skuModelArr = GoodsSkuService::getSkuImage($skuIds,$companyId);
            $skuModelArr = ArrayUtils::index($skuModelArr,'id');
        }
        foreach ($couponBatchList as $k=>&$v){
            if ($v['use_limit_type']==Coupon::LIMIT_TYPE_ALL){
                $v['icon'] = Common::generateAbsoluteUrl("coupon/coupon_type_all.png");
            }
            else if ($v['use_limit_type']==Coupon::LIMIT_TYPE_OWNER){
                $v['icon'] = Common::generateAbsoluteUrl("coupon/coupon_type_owner.png");
            }
            else if ($v['use_limit_type']==Coupon::LIMIT_TYPE_SORT){
                $v['icon'] = Common::generateAbsoluteUrl("coupon/coupon_type_sort.png");
            }
            else if ($v['use_limit_type']==Coupon::LIMIT_TYPE_GOODS_SKU){
                if (key_exists($v['use_limit_type_params'],$skuModelArr)){
                    $skuModel = $skuModelArr[$v['use_limit_type_params']];
                    $v['icon'] = Common::generateAbsoluteUrl(StringUtils::filterFirstNotBlank($skuModel['sku_img'],$skuModel['goods_img'],"coupon/coupon_type_sku.png"));
                }
                else{
                    $v['icon'] = Common::generateAbsoluteUrl("coupon/coupon_type_sku.png");
                }
            }
        }
        return $couponBatchList;
    }


    /**
     * 补全优惠券可领取的状态
     * @param $couponBatchArr
     * @param $company_id
     * @param $customerModel
     * @return array
     */
    private static function completeDrawStatus($couponBatchArr,$company_id,$customerModel){
        if (empty($couponBatchArr)){
            return [];
        }
        foreach ($couponBatchArr as $k=>$v){
            list($result,$drawStatus) =self::tryDrawCoupon($company_id,$customerModel,$v,1,true);
            if ($result){
                $v['can_draw'] = true;
            }
            else{
                $v['can_draw'] = false;
            }
            if ($v['amount']<1){
                $v['draw_percent'] = 100;
            }
            else{
                $v['draw_percent'] = Common::calcPercentInt($v['draw_amount'],$v['amount']);
            }
            $v['draw_status'] = $drawStatus;
            $v['draw_status_text'] = ArrayUtils::getArrayValue($drawStatus, CouponBatch::$drawStatusArr);
            $couponBatchArr[$k] = $v;
        }
        return $couponBatchArr;
    }

    /**
     * 领券中心配置
     * @return array
     */
    public static function couponCenterConfig(){
        $couponTypeArr = [];
        $couponType1 = [
            'name'=>'全场优惠',
            'type'=>Coupon::LIMIT_TYPE_ALL,
            'urls'=>[
                Common::generateAbsoluteUrl("coupon/coupon_center.png")
            ],
        ];
        $couponTypeArr[] = $couponType1;

        $couponType2 = [
            'name'=>'标品商圈',
            'type'=>Coupon::LIMIT_TYPE_OWNER,
            'urls'=>[
                Common::generateAbsoluteUrl("coupon/coupon_center.png")
            ],
        ];
        $couponTypeArr[] = $couponType2;


        $couponType3 = [
            'name'=>'特殊分类',
            'type'=>Coupon::LIMIT_TYPE_SORT,
            'urls'=>[
                Common::generateAbsoluteUrl("coupon/coupon_center.png")
            ],
        ];
        $couponTypeArr[] = $couponType3;

        $couponType4 = [
            'name'=>'精选商品',
            'type'=>Coupon::LIMIT_TYPE_GOODS_SKU,
            'urls'=>[
                Common::generateAbsoluteUrl("coupon/coupon_center.png")
            ],
        ];
        $couponTypeArr[] = $couponType4;

        $configs = ['coupon_types'=>$couponTypeArr];
        return $configs;
    }


    // 获取代理商可用新人优惠券
    public static function getNewBatchCoupon($companyId){
        return CouponBatch::find()->where(['coupon_type'=>CouponBatch::COUPON_NEW,'company_id'=>$companyId])
        ->andWhere([
            'and',
            'draw_amount < amount',
            ['status'=>CouponBatch::STATUS_ACTIVE],
            ['<=','draw_start_time',DateTimeUtils::parseStandardWLongDate()],
            ['>=','draw_end_time',DateTimeUtils::parseStandardWLongDate()]
        ])->all();
    }

    // 根据user获取Customer信息
    public static function getCustomerByUser($userM){
       if(!$userM->user_info_id)  return false;  
       $userinfo = UserInfo::find()->where(['id'=>$userM->user_info_id])->one();
       if($userinfo){
           return CustomerService::getActiveModelByUserInfoId($userinfo->id,true)??false;
       }
       return false;
    }

    /**
     * [automaticDrawCoupon 自动发放新人优惠券]
     * @param  [type] $userM       [description]
     * @param  [type] $delivery_id [description]
     * @return [type]              [description]
     */
    public static function automaticDrawCoupon()
    {  
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            $customerM  = FrontendCommon::requiredCustomer();
            $deliveryM  = FrontendCommon::requiredDelivery();
            if(empty($customerM)){
               return [false,'客户未注册'];
            }
            if(empty($deliveryM)){
               return [false,'团点不存在'];
            }
            list($result,$error) = self::drawNewCoupon($deliveryM,$customerM);
            $transaction->commit();
            GlobalLog::getInstance()->saveLog([$result,$error,'customerId'=>$customerM->id,'deliveryId'=>$deliveryM->id,'companyId'=>$deliveryM->company_id]);
            return [$result,$error];
        }
        catch (\Throwable $exception) {
            $transaction->rollBack();
            $msg = "error:" . $exception->getMessage()
                . "\nfile:" . $exception->getFile()
                . "\nline:" . $exception->getLine();
            GlobalLog::getInstance()->saveLog($msg);
            return [false,$exception->getMessage().'---automaticDrawCoupon'];
        }
    }

    /**
     * [drawNewCoupon 自动领取新人券]
     * @param  [type]  $deliveryM [description]
     * @param  [type]  $customerM [description]
     * @param  integer $num       [description]
     * @param  string  $remark    [description]
     * @return [type]             [description]
     */
    public static function drawNewCoupon($deliveryM,$customerM,$num=1,$remark='自动领取新人券'){
        $companyId = $deliveryM->company_id;
        $customerId = $customerM->id;

        /*校验 新人优惠券只限新人领取*/
        if(!AdminUserService::userGetCouponByCompany($customerId,$companyId)){
            return [false,'只限新用户领取'];
        }

        $batchs = self::getNewBatchCoupon($companyId);
        if(!$batchs){
            return [false,'没有可用优惠券'];
        }
        
        // 每一种券发一张
        $coupons = [];
        foreach ($batchs as $key => $batch) {
            parent::newCouponReduceStock($batch['id'],$num,$batch['version']);
            parent::addCouponBatchDrawLog($batch,$num,$customerM['id'],$customerM['id'],$customerM['nickname'],1,$remark);
            $coupon_no  = parent::addCoupon($batch,$customerM['id'],$customerM['id'],$customerM['nickname'],1,$remark);
            $coupons[] = $coupon_no;
        }
        return [true,['领取新人优惠券成功',$coupons]];
    }

    /**
     * 查找未提醒过的优惠券，并标记为已提醒
     * @param $customerId
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUnRemindCouponAndSetReminded($customerId,$companyId){
        $couponList = Coupon::find()->where([
            'and',
            [
                'coupon_type'=>CouponBatch::COUPON_NEW,
                'is_remind'=>Coupon::IS_REMIND_FALSE,
                'company_id'=>$companyId,
                'customer_id'=>$customerId,
                'status'=>Coupon::STATUS_ACTIVE,
            ],
            ['<=','start_time',DateTimeUtils::parseStandardWLongDate()],
            ['>=','end_time',DateTimeUtils::parseStandardWLongDate()]
        ])->asArray()->all();
        if (empty($couponList)){
            return [];
        }

        $couponList = CouponDisplayDomainService::batchDefineDescVO($couponList);
        $couponNos = ArrayUtils::getColumnWithoutNull("coupon_no",$couponList);
        Coupon::updateAll(['is_remind'=>Coupon::IS_REMIND_TRUE,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['coupon_no'=>$couponNos,'is_remind'=>Coupon::IS_REMIND_FALSE]);
        return $couponList;
    }

    /**
     * 查找未使用的优惠券
     * @param $customerId
     * @param $companyId
     * @param $couponType
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUnUsedCoupon($customerId,$companyId,$couponType){
        $conditions = [
            'and',
            [
                'is_remind'=>Coupon::IS_REMIND_FALSE,
                'company_id'=>$companyId,
                'customer_id'=>$customerId,
                'status'=>Coupon::STATUS_ACTIVE,
            ],
            ['<=','start_time',DateTimeUtils::parseStandardWLongDate()],
            ['>=','end_time',DateTimeUtils::parseStandardWLongDate()]
        ];
        if (StringUtils::isNotBlank($couponType)){
            $conditions[] = ['coupon_type'=>$couponType];
        }
        $couponList = Coupon::find()->where($conditions)->asArray()->all();
        if (empty($couponList)){
            return [];
        }
        $couponList = CouponDisplayDomainService::batchDefineDescVO($couponList);
        return $couponList;
    }
}