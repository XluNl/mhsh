<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use common\models\GroupActive;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;

class GroupActiveService extends \common\services\GroupActiveService {


    /**
     * @param $id
     * @param $companyId
     * @param $validateException
     * @return array|bool|GroupActive|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id, $companyId, $validateException){
        $model = GroupActive::find()->where(['id' => $id,'company_id'=>$companyId])->with(['schedule.goods', 'schedule.goodsSku'])->one();
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 操作
     * @param $goodsId
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operate($goodsId,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[GroupActive::STATUS_UP,GroupActive::STATUS_DOWN,GroupActive::STATUS_DELETED]),$validateException);
        $count = GroupActive::updateAll(['status'=>$commander],['id'=>$goodsId,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 校验归属问题
     * @param GroupActive $model
     * @return bool
     */
    public static function validateOwnerTypeAndSave(GroupActive &$model){
        if (!$model->validate()){
            return false;
        }
        $scheduleModel = GoodsScheduleService::getActiveGoodsSchedule($model->schedule_id,$model->company_id,false);
        if (empty($scheduleModel)){
            $model->addError("schedule_id","商品不存在");
            return false;
        }
        if ($scheduleModel['owner_type']!=$model->owner_type){
            $model->addError("owner_type","商品归属错误");
            return false;
        }
        if (!$model->save()){
            return false;
        }
        return true;
    }

}