<?php


namespace common\services;


use common\models\AllianceChannel;
use yii\db\Query;

class AllianceChannelService
{

    public static function getChannelByAlliance($allianceId,$companyId=null,$id=null,$model=false){
        $conditions = ['alliance_id'=>$allianceId];
        if (!empty($id)){
            $conditions['id']=$id;
        }
        if (!empty($companyId)){
            $conditions['company_id']=$companyId;
        }
        if ($model){
            $result = AllianceChannel::find()->where($conditions)->one();
            return $result;
        }
        else{
            $result = (new Query())->from(AllianceChannel::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
}