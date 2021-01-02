<?php


namespace common\services;

use common\models\Alliance;
use common\models\CloseApply;
use common\models\Common;
use common\models\CommonStatus;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class CloseApplyService
{

    /**
     * 根据ID获取
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|CloseApply|\yii\db\ActiveRecord|null
     */
    public static function getModel($id,$company_id = null, $model = false){
        $conditions = ['id' => $id,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return CloseApply::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(CloseApply::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    /**
     * 获取申请记录
     * @param $userId
     * @param $bizType
     * @param $bizId
     * @param bool $model
     * @return array|bool|CloseApply|\yii\db\ActiveRecord|null
     */
    public static function getModelByIdAndUserId($userId,$bizType,$bizId,$model=false){
        $condition = ['biz_id'=>$bizId,'status'=>CommonStatus::STATUS_ACTIVE];
        if (StringUtils::isNotBlank($userId)){
            $condition['user_id'] = $userId;
        }
        if (StringUtils::isNotBlank($bizType)){
            $condition['biz_type'] = $bizType;
        }
        if ($model){
            $model = CloseApply::find()->where($condition)->one();
        }
        else{
            $model = (new Query())->from(CloseApply::tableName())->where($condition)->one();
            $model = ($model===false)?null:$model;
        }
        return $model;
    }

    /**
     * 撤销申请
     * @param $id
     * @param $userId
     * @param $bizType
     * @param $bizId
     * @return bool
     */
    public static function cancelApply($id,$userId,$bizType,$bizId){
        $condition = ['id'=>$id,'user_id'=>$userId,'biz_type'=>$bizType,'biz_id'=>$bizId,'action'=>CloseApply::ACTION_APPLY];
        $uploadCount = CloseApply::updateAll(['action'=>CloseApply::ACTION_CANCEL],$condition);
        return $uploadCount>0;
    }

    /**
     * 批量设置申请状态
     * @param $list
     * @return array
     */
    public static function batchSetVOText($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::setVOText($v);
            $list[$k] = $v;
        }
        return $list;
    }


    /**
     * 设置申请状态
     * @param $model
     * @return mixed
     */
    public static function setVOText($model){
        if (empty($model)){
            return $model ;
        }
        if (key_exists('action',$model)){
            $model['action_text'] = ArrayUtils::getArrayValue($model['action'],CloseApply::$actionArr,'');
        }
        return $model;
    }

    /**
     * 移除上一次无效申请
     * @param $userId
     * @param $bizType
     * @param $bizId
     * @param $companyId
     * @return bool
     */
    public static function removePreApply($userId,$bizType,$bizId,$companyId){
        $condition = ['company_id'=>$companyId,'user_id'=>$userId,'biz_type'=>$bizType,'biz_id'=>$bizId,'status'=>CommonStatus::STATUS_ACTIVE,'action'=>[CloseApply::ACTION_CANCEL,CloseApply::ACTION_DENY]];
        $uploadCount = CloseApply::updateAll(['status'=>CommonStatus::STATUS_DISABLED],$condition);
        return $uploadCount>0;
    }

    /**
     * 判断是否允许申请
     * @param $userId
     * @param $bizType
     * @param $bizId
     * @param $companyId
     * @return array
     */
    public static function checkApplying($userId,$bizType,$bizId,$companyId){
        $closeApply = self::getModelByIdAndUserId($userId,$bizType,$bizId);
        if (!empty($closeApply)){
            if ($closeApply['action']==CloseApply::ACTION_APPLY){
                return [false,'正在申请中，请勿重复提交'];
            }
            if ($closeApply['action']==CloseApply::ACTION_ACCEPT){
                return [false,'申请已通过，请勿重复提交'];
            }
        }
        if (CloseApply::APPLY_TYPE_HA==$bizType){
            $alliance = AllianceService::getActiveModel($bizId,$companyId);
            if (empty($alliance)){
                return [false,'联盟点不存在'];
            }
            if ($alliance['status']==Alliance::STATUS_OFFLINE){
                return [false,'联盟点已关闭，请勿重复申请'];
            }
        }
        else{
            return [false,'未知申请类型'];
        }
        return [true,''];
    }
}