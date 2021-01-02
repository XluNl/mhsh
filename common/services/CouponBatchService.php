<?php


namespace common\services;


use common\models\Common;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\CouponBatchDrawLog;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;

class CouponBatchService
{
    /**
     * 生成领用规则描述信息
     * @param $draw_limit_type
     * @param $draw_limit_type_params
     * @return string
     */
    public static function generateDrawDesc($draw_limit_type,$draw_limit_type_params){
        return ArrayUtils::getArrayValue($draw_limit_type,CouponBatch::$drawTypeLimitArr).$draw_limit_type_params.'次';
    }

    /**
     * 根据ID获取数据
     * @param $id
     * @param $statusArr
     * @param $company_id
     * @param bool $model
     * @return array|bool|CouponBatch|null
     */
    public static function getDisplayModel($id, $statusArr, $company_id, $model = false){
        $conditions = ['id' => $id,'company_id'=>$company_id];
        if (StringUtils::isNotBlank($statusArr)){
            $conditions['status'] = $statusArr;
        }
        if ($model){
            return CouponBatch::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(CouponBatch::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据batchNo查找
     * @param $batchNo
     * @param $statusArr
     * @param $company_id
     * @param bool $model
     * @return array|bool|CouponBatch|null
     */
    public static function getDisplayModelByBatchNo($batchNo, $statusArr, $company_id,$coupon_type,$model = false){
        $conditions = ['batch_no' => $batchNo,'coupon_type' => $coupon_type];
        if ($statusArr!==null){
            $conditions['status'] = $statusArr;
        }
        if (StringUtils::isNotBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return CouponBatch::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(CouponBatch::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 获取有效的优惠券
     * @param $batchNos
     * @param $companyId
     * @return array
     */
    public static function getActiveModelByBatchNos($batchNos,$companyId){
        $conditions = ['batch_no' => $batchNos,'status'=>CouponBatch::STATUS_ACTIVE];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $result = (new Query())->from(CouponBatch::tableName())->where($conditions)->all();
        return $result;
    }


    /**
     * 领取优惠券
     * @param $companyId
     * @param $customerId
     * @param $batchNo
     * @param $num
     * @param $isPublic
     * @param $operatorId
     * @param $operatorName
     * @param $operatorType
     * @param string $remark
     * @return array
     */
    public static function drawCoupon($companyId, $customerId, $batchNo, $num, $isPublic, $operatorId, $operatorName, $operatorType, $remark=''){
        $couponBatchModel = self::getDisplayModelByBatchNo($batchNo,CouponBatch::STATUS_ACTIVE,$companyId,CouponBatch::COUPON_PLAN,true);
        if (empty($couponBatchModel)){
            return [false,'优惠券批次不存在'];
        }
        $customerModel = CustomerService::getActiveModel($customerId);
        if (empty($customerModel)){
            return [false,'客户未注册'];
        }

        /*校验 新人优惠券只限新人领取*/
        if($couponBatchModel['coupon_type']==CouponBatch::COUPON_NEW && !AdminUserService::userGetCouponByCompany($customerId,$companyId)){
            return [false,'只限新用户领取'];
        }

        /* 校验领取条件*/
        list($validateDrawLimitResult,$validateDrawLimitError) = self::validateDrawLimit($couponBatchModel,$customerModel,$isPublic);
        if ($validateDrawLimitResult==false){
            return [$validateDrawLimitResult,$validateDrawLimitError];
        }

        /* 校验最大领取数量*/
        list($validateDrawTimeLimitResult,$validateDrawTimeLimitError) = self::validateDrawTimeLimit($couponBatchModel,$customerModel);
        if ($validateDrawTimeLimitResult==false){
            return [$validateDrawTimeLimitResult,$validateDrawTimeLimitError];
        }


        list($reduceStockResult,$reduceStockError) = self::reduceStock($couponBatchModel['id'],$num,$couponBatchModel['version']);
        if (!$reduceStockResult){
            return [false,$reduceStockError];
        }
        list($addCouponBatchDrawLogResult,$addCouponBatchDrawLogError) = self::addCouponBatchDrawLog($couponBatchModel,$num,$customerModel['id'],$operatorId,$operatorName,$operatorType,$remark);
        if (!$addCouponBatchDrawLogResult){
            return [false,$addCouponBatchDrawLogError];
        }
        $couponNos = [];
        for ($i=0;$i<$num;$i++){
            list($addCouponResult,$addCouponError) = self::addCoupon($couponBatchModel,$customerModel['id'],$operatorId,$operatorName,$operatorType,$remark);
            if (!$addCouponResult){
                return [false,$addCouponError.$num];
            }
            $couponNos[] = $addCouponError;
        }
        return [true,implode(",", $couponNos)];
    }

    /**
     * 尝试领取优惠券
     * @param $company_id
     * @param $customerModel
     * @param $couponBatchModel
     * @param $num
     * @param $isPublic
     * @return array
     */
    protected static function tryDrawCoupon($company_id,$customerModel,$couponBatchModel,$num,$isPublic){
        /* 校验领取条件*/
        list($validateDrawLimitResult,$validateDrawLimitError) = self::validateDrawLimit($couponBatchModel,$customerModel,$isPublic);
        if ($validateDrawLimitResult===false){
            return [false,CouponBatch::TRY_DRAW_RESULT_FORBIDDEN];
        }

        /* 校验最大领取数量*/
        list($validateDrawTimeLimitResult,$validateDrawTimeLimitError) = self::validateDrawTimeLimit($couponBatchModel,$customerModel);
        if ($validateDrawTimeLimitResult===false){
            return [false,CouponBatch::TRY_DRAW_RESULT_DRAWN];
        }
        /* 校验优惠券批次库存*/
        list($validateCouponBatchStockLimitResult,$validateCouponBatchStockLimitResultError) = self::validateCouponBatchStockLimit($couponBatchModel,$num);
        if ($validateCouponBatchStockLimitResult===false){
            return [false,CouponBatch::TRY_DRAW_RESULT_OUT_STOCK];
        }
        return [true,CouponBatch::TRY_DRAW_RESULT_OK];
    }


    /**
     * 校验领取条件
     * @param $couponBatchModel
     * @param $customerModel
     * @param $isPublic
     * @return array
     */
    private static function validateDrawLimit($couponBatchModel,$customerModel,$isPublic){
        $nowTime = time();
        if (!DateTimeUtils::isBetween($nowTime,$couponBatchModel['draw_start_time'],$couponBatchModel['draw_end_time'])){
            return  [false,'未在领取期间'];
        }
        if ($couponBatchModel['draw_customer_type']!=CouponBatch::DRAW_CUSTOMER_TYPE_ALL){
            $userInfoModel = UserInfoService::getActiveModel($customerModel['user_id']);
            if (empty($userInfoModel)){
                return [false,'用户信息不存在'];
            }
            $phone = $userInfoModel['phone'];
            if ($couponBatchModel['draw_customer_type']==CouponBatch::DRAW_CUSTOMER_TYPE_WHITE){
                if (!StringUtils::containsSubString($couponBatchModel['draw_customer_phones'],$phone)){
                    return [false,'不在名单中W'];
                }

            }
            else if ($couponBatchModel['draw_customer_type']==CouponBatch::DRAW_CUSTOMER_TYPE_BLACK){
                if (StringUtils::containsSubString($couponBatchModel['draw_customer_phones'],$phone)){
                    return [false,'在名单中B'];
                }
            }
        }

        if ($isPublic!=$couponBatchModel['is_public']){
            return [false,'领取渠道不符合'];
        }
        return [true,''];
    }


    /**
     * 校验领取次数
     * @param $couponBatchModel
     * @param $customerModel
     * @return array
     */
    private static function validateDrawTimeLimit($couponBatchModel,$customerModel){
        $passCount = CouponBatchDrawLogService::calc($couponBatchModel['draw_limit_type'],$customerModel['id'],$couponBatchModel['id']);
        if ($passCount>=$couponBatchModel['draw_limit_type_params']){
            return [false,'超过最大可领张数'];
        }
        return [true,''];
    }

    /**
     * 判断是否可以再领取
     * @param $couponBatchModel CouponBatch
     * @param $num
     * @return array
     */
    private static function validateCouponBatchStockLimit($couponBatchModel,$num){
        if ($couponBatchModel['draw_amount']+$num>$couponBatchModel['amount']){
            return [false,'超过最大可领张数'];
        }
        return [true,''];
    }


    private static function reduceStock($batchId,$num,$version){
        $skuUpdateCount = CouponBatch::updateAllCounters(['draw_amount'=>$num,'version'=>1],['and',['id'=>$batchId,'version'=>$version],"amount-draw_amount>={$num}"]);
        if ($skuUpdateCount>0){
            return [true,'更新成功'];
        }
        else{
            return [false,'库存扣减失败'];
        }
    }

    public static function newCouponReduceStock($batchId,$num,$version){
        return self::reduceStock($batchId,$num,$version);
    }

    public static function addCouponBatchDrawLog($couponBatchModel,$num,$customerId,$operatorId,$operatorName,$operatorType,$remark){
        $log = new CouponBatchDrawLog();
        $log->company_id = $couponBatchModel['company_id'];
        $log->batch_id = $couponBatchModel['id'];
        $log->num = $num;
        $log->customer_id = $customerId;
        $log->operator_id = $operatorId;
        $log->operator_name = $operatorName;
        $log->operator_type = $operatorType;
        $log->remark = $remark;
        if ($log->save()){
            return [true,'添加日志成功'];
        }
        else{
            return [false,'添加日志失败'];
        }
    }

    /**
     * [addCoupon 自动发放新人优惠券]
     * @param  [type] $couponBatchModel  [次参数为一个CouponBatch对象,不能为数组]
     * @param  [type] $delivery_id [description]
     * @return [type]              [description]
     */
    public static function addCoupon(CouponBatch $couponBatchModel,$customerId,$operatorId,$operatorName,$operatorType,$remark){
        $couponBatchModel->decodeUserTimeFeature(true);
        $coupon = new Coupon();
        $coupon->coupon_no = $coupon->generate_no();
        $coupon->company_id = $couponBatchModel['company_id'];
        $coupon->customer_id = $customerId;
        $coupon->name = $couponBatchModel['coupon_name'];
        $coupon->startup = $couponBatchModel['startup'];
        $coupon->discount = $couponBatchModel['discount'];
        $coupon->type = $couponBatchModel['type'];
        $coupon->start_time = $couponBatchModel['use_start_time'];
        $coupon->end_time = $couponBatchModel['use_end_time'];
        $coupon->status = Coupon::STATUS_ACTIVE;
        $coupon->batch = $couponBatchModel['id'].'';
        $coupon->remark = $couponBatchModel['remark'];
        $coupon->limit_type = $couponBatchModel['use_limit_type'];
        $coupon->limit_type_params = $couponBatchModel['use_limit_type_params'];
        $coupon->restore = $couponBatchModel['restore'];
        $coupon->draw_operator_id = $operatorId;
        $coupon->draw_operator_name = $operatorName;
        $coupon->draw_operator_type = $operatorType;
        $coupon->owner_type = $couponBatchModel['owner_type'];
        $coupon->owner_id = $couponBatchModel['owner_id'];
        $coupon->coupon_type = $couponBatchModel['coupon_type'];
        $coupon->remark = $remark;
        if ($coupon->save()){
            return [true,$coupon->coupon_no];
        }
        else{
            \Yii::error($coupon->errors);
            return [false,'创建优惠券失败'];
        }
    }

    public static function statusOperationP($companyId, $ownerType, $ownerId, $id, $status){
        if (!key_exists($status,CouponBatch::$statusArr)){
            return [false,'不支持的操作'];
        }
        CouponBatch::updateAll(['status'=>$status,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],[
            'id'=>$id,
            'company_id'=>$companyId,
            'owner_type'=>$ownerType,
            'owner_id'=>$ownerId
        ]);
        return [true,""];
    }

    public static function batchSetDisplayVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::setDisplayVO($v);
            $list[$k] = $v;
        }
        return $list;
    }


    public static function setDisplayVO($model){
        if (empty($model)){
            return [];
        }
        $model['status_text'] = ArrayUtils::getArrayValue($model['status'],CouponBatch::$statusArr);
        $model['restore_text'] = ArrayUtils::getArrayValue($model['restore'],Coupon::$restoreArr);
        $model['draw_limit_text'] = CouponBatchService::generateDrawDesc($model['draw_limit_type'] ,$model['draw_limit_type_params']);
        $model['use_limit_text'] = CouponService::generateCouponDesc($model['type'],$model['startup'],$model['discount'],$model['use_limit_type'],$model['use_limit_type_params']);
        $model['remain'] = $model['amount']-$model['draw_amount'];
        $model['is_public_text'] = ArrayUtils::getArrayValue($model['is_public'],CouponBatch::$isPublicArr);
        $model['is_pop_text'] = ArrayUtils::getArrayValue($model['is_pop'],CouponBatch::$isPopArr);
        $model['draw_customer_text'] = ArrayUtils::getArrayValue($model['draw_customer_type'],CouponBatch::$drawCustomerTypeArr);
        return $model;
    }

}