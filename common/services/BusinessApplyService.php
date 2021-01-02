<?php


namespace common\services;

use common\models\BusinessApply;
use common\models\CommonStatus;
use common\models\Popularizer;
use common\utils\StringUtils;
use yii\db\Query;

class BusinessApplyService
{
    /**
     * 根据ID获取
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|BusinessApply|\yii\db\ActiveRecord|null
     */
    public static function getModel($id,$company_id = null, $model = false){
        $conditions = ['id' => $id,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return BusinessApply::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(BusinessApply::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 获取申请记录
     * @param $id
     * @param $userId
     * @param bool $model
     * @return array|bool|BusinessApply|\yii\db\ActiveRecord|null
     */
    public static function getModelByIdAndUserId($id,$userId,$model=false){
        if ($model){
            $model = BusinessApply::find()->where(['id'=>$id,'user_id'=>$userId])->one();
        }
        else{
            $model = (new Query())->from(BusinessApply::tableName())->where(['id'=>$id,'user_id'=>$userId])->one();
            $model = ($model===false)?null:$model;
        }
        return $model;
    }



    /**
     * 撤销申请
     * @param $id
     * @param $userId
     * @return bool
     */
    public static function cancelApply($id,$userId){
        $uploadCount = BusinessApply::updateAll(['action'=>BusinessApply::ACTION_CANCEL],['id'=>$id,'user_id'=>$userId]);
        return $uploadCount>0;
    }



    public static function checkApplying($userId,$type,$companyId){
        if (BusinessApply::APPLY_TYPE_POPULARIZER==$type){
            $popularizer = (new Query())->from(Popularizer::tableName())->where(['user_id'=>$userId,'company_id'=>$companyId])->one();
            if (StringUtils::isNotEmpty($popularizer)){
                return [false,'已注册过推广团长，无需重复申请'];
            }
            $exModel = (new Query())->from(BusinessApply::tableName())->where(['user_id'=>$userId,'action'=>[BusinessApply::ACTION_APPLY],'type'=>BusinessApply::APPLY_TYPE_POPULARIZER,'status'=>CommonStatus::STATUS_ACTIVE,'company_id'=>$companyId])->count();
            if ($exModel>0){
                return [false,'正在申请推广团长，请耐心等待'];
            }
        }
        else if (BusinessApply::APPLY_TYPE_DELIVERY==$type){
            $exModel = (new Query())->from(BusinessApply::tableName())->where(['user_id'=>$userId,'action'=>BusinessApply::ACTION_APPLY,'type'=>BusinessApply::APPLY_TYPE_DELIVERY,'status'=>CommonStatus::STATUS_ACTIVE,'company_id'=>$companyId])->count();
            if ($exModel>0){
                return [false,'正在申请配送团长，请耐心等待'];
            }
        }
        else if (BusinessApply::APPLY_TYPE_HA==$type){
            $exModel = (new Query())->from(BusinessApply::tableName())->where(['user_id'=>$userId,'action'=>BusinessApply::ACTION_APPLY,'type'=>BusinessApply::APPLY_TYPE_HA,'status'=>CommonStatus::STATUS_ACTIVE,'company_id'=>$companyId])->count();
            if ($exModel>0){
                return [false,'正在申请异业联盟商户，请耐心等待'];
            }
        }
        else{
            return [false,'未知申请类型'];
        }
        return [true,''];
    }
}