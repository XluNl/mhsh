<?php


namespace console\services;


use common\models\CommonStatus;
use common\models\CustomerInvitation;
use common\models\CustomerInvitationLevel;
use yii\db\Query;
use yii\helpers\Json;

class CustomerInvitationLevelService extends \common\services\CustomerInvitationLevelService
{
    public static function refreshCount(){
        $customerInvitationTable =  CustomerInvitation::tableName();
        $error = [];
        $data = (new Query())->from(['a'=>$customerInvitationTable])
            ->select([
                "COUNT(a.customer_id)  as one_level_num",
                "COUNT(b.customer_id)  as two_level_num",
                "a.parent_id as customer_id"
            ])
            ->leftJoin(['b'=>$customerInvitationTable],"b.parent_id = a.customer_id")
            ->where([
                'and',
                ["a.status"=>CommonStatus::STATUS_ACTIVE],
                [
                    'or',
                    ["b.status"=>CommonStatus::STATUS_ACTIVE],
                    "b.status IS NULL",
                ]
            ])
            ->groupBy("a.parent_id")->all();
        if (!empty($data)){
            foreach ($data as $v){
                $model = parent::getModelCustomerId($v['customer_id'],true);
                if (empty($model)){
                    $model = new CustomerInvitationLevel();
                    $model->customer_id = $v['one_level_num'];
                }
                $model->one_level_num = $v['one_level_num'];
                $model->two_level_num = $v['two_level_num'];
                if ($model->one_level_num>=2){
                    $model->level = CustomerInvitationLevel::LEVEL_TWO;
                }
                else if ($model->one_level_num>=1){
                    $model->level = CustomerInvitationLevel::LEVEL_ONE;
                }
                else{
                    $model->level = CustomerInvitationLevel::LEVEL_NORMAL;
                }
                if (!$model->save()){
                    $error[] = ['customerId'=> $v['customer_id'],'error'=>Json::encode($v->errors)];
                }
            }
        }
        return [count($data),$error];
    }
}