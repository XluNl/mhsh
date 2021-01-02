<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\BonusBatch;
use common\models\BonusBatchDrawLog;
use common\models\DistributeBalanceItem;
use common\models\RoleEnum;
use yii\data\ActiveDataProvider;

class BonusBatchService  extends \common\services\BonusBatchService
{

    /**
     * 获取可展示，非空校验
     * @param $id
     * @param $validateException
     * @param bool $model
     * @return array|bool|BonusBatch|null
     */
    public static function requireDisplayModel($id, $validateException, $model = false){
        $model = self::getDisplayModel($id,[BonusBatch::STATUS_ACTIVE,BonusBatch::STATUS_DISABLED],$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 获取进行中，非空校验
     * @param $id
     * @param $validateException
     * @param bool $model
     * @return array|bool|BonusBatch|null
     */
    public static function requireActiveModel($id, $validateException, $model = false){
        $model = self::getDisplayModel($id,BonusBatch::STATUS_ACTIVE,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 奖励金活动状态操作
     * @param $id
     * @param $commander
     * @param $validateException
     */
    public static function operate($id,$commander,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,BonusBatch::$statusArr),$validateException);
        $count = BonusBatch::updateAll(['status'=>$commander],['id'=>$id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }


    public static function drawManualBonus($company_id, $bizType, $bizId, $batchNo, $num, $operatorId, $operatorName, $remark){
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = parent::drawBonus($company_id,$bizType,$bizId,BonusBatchDrawLog::DRAW_TYPE_MANUAL_DRAW, null,DistributeBalanceItem::TYPE_DRAW_BONUS, 0,$batchNo,$num,$operatorId,$operatorName,RoleEnum::ROLE_ADMIN,$remark);
            BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::DRAW_BONUS_ERROR,$error));
            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            \yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,BStatusCode::createExpWithParams(BStatusCode::DRAW_BONUS_ERROR,$e->getMessage()));
        }
    }


    /**
     * 补全账户名称
     * @param $dataProvider ActiveDataProvider
     * @return mixed
     */
    public static function completeBizName($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        $models = BizTypeService::completeByBizType($models);
        $dataProvider->setModels($models);
        return $dataProvider;
    }
}