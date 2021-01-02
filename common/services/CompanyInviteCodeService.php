<?php


namespace common\services;


use common\models\CompanyInviteCode;

class CompanyInviteCodeService
{

    public static function getModelById($companyId){
        $model = CompanyInviteCode::find()->where(['company_id'=>$companyId])->one();
        return $model===false?null:$model;
    }
}