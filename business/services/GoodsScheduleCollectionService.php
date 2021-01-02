<?php


namespace business\services;


use business\models\BusinessCommon;
use business\utils\ExceptionAssert;
use business\utils\exceptions\BusinessException;
use business\utils\StatusCode;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsScheduleCollection;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class GoodsScheduleCollectionService extends \common\services\GoodsScheduleCollectionService
{


    public static function getFilterList($deliveryId,$companyId,$name=null,$startTime=null,$endTime=null,$pageNo=1,$pageSize=20){
        $models = parent::getModels(GoodsConstantEnum::OWNER_DELIVERY,$deliveryId,$companyId,$name,$startTime,$endTime,$pageNo,$pageSize);
        //补全基本信息
        $models = self::completeSimpleStatistic($deliveryId, $companyId, $models);
        $models = GoodsScheduleCollectionService::batchSetDisplayVO($models);
        return $models;
    }

    /**
     * @param $id
     * @param $deliveryId
     * @param $companyId
     * @param bool $model
     * @return array|bool|\common\models\GoodsScheduleCollection|\yii\db\ActiveRecord|null
     */
    public static function getInfo($id, $deliveryId, $companyId, $model=false){
        $model = parent::getActiveModel($id,$companyId,GoodsConstantEnum::OWNER_DELIVERY,$deliveryId,$model);
        ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::GOODS_SCHEDULE_COLLECTION_NOT_EXIST));
        return $model;
    }

    /**
     * @param $model GoodsScheduleCollection
     */
    public static function modify($model){
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            if (StringUtils::isNotBlank($model->id)){
                GoodsSchedule::updateAll([
                    'display_start'=>$model->display_start,
                    'display_end'=>$model->display_end,
                    'online_time'=>$model->online_time,
                    'offline_time'=>$model->offline_time
                ],['collection_id'=>$model->id,'company_id'=>$model->company_id]);
            }
            ExceptionAssert::assertTrue($model->save(),BusinessException::create(BusinessCommon::getModelErrors($model)));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_COLLECTION_MODIFY,$e->getMessage()));
        }
    }

    /**
     * 批量操作排期状态
     * @param $deliveryModel
     * @param $id
     * @param $status
     */
    public static function statusOperation($deliveryModel,$id,$status){
        list($result,$errMsg) = parent::statusOperationP($deliveryModel['company_id'],GoodsConstantEnum::OWNER_DELIVERY,$deliveryModel['id'],$id,$status);
        ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_COLLECTION_STATUS_OPERATION_ERROR,$errMsg));
    }


    /**
     * 排期批量增加商品
     * @param $deliveryModel
     * @param $collectionId
     * @param $goodsData
     * @param $goodsType
     */
    public static function batchAddGoods($deliveryModel,$collectionId,$goodsData,$goodsType){
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            list($result,$errMsg) = parent::batchAddGoodsP($deliveryModel['company_id'],GoodsConstantEnum::OWNER_DELIVERY,$deliveryModel['id'],$collectionId,$goodsData,$goodsType,$deliveryModel['id'],$deliveryModel['nickname']);
            ExceptionAssert::assertTrue($result,BusinessException::create($errMsg));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_ADD_GOODS_ERROR,$e->getMessage()));
        }
    }

    /**
     * 补全基本信息
     * @param $deliveryId
     * @param $companyId
     * @param array $models
     * @return array
     */
    public static function completeSimpleStatistic($deliveryId, $companyId, array $models)
    {
        $scheduleStatusUp = GoodsConstantEnum::STATUS_UP;
        if (!empty($models)) {
            $collectionIds = ArrayUtils::getColumnWithoutNull('id', $models);
            $scheduleGoodsNum = (new Query())->from(GoodsSchedule::tableName())
                ->select([
                    'collection_id',
                    "COUNT(id) as goods_num",
                    "SUM(schedule_sold) as sold_num",
                    "SUM(case when schedule_status = {$scheduleStatusUp}  then 1 else 0 end) as schedule_up_count",
                ])
                ->where([
                    'collection_id' => $collectionIds,
                    'company_id' => $companyId,
                    'owner_type' => GoodsConstantEnum::OWNER_DELIVERY,
                    'owner_id' => $deliveryId,
                ])->groupBy('collection_id')->all();
            $scheduleGoodsNum = ArrayUtils::index($scheduleGoodsNum, 'collection_id');
            foreach ($models as $k => $v) {
                if (key_exists($v['id'], $scheduleGoodsNum)) {
                    $v['goods_num'] = $scheduleGoodsNum[$v['id']]['goods_num'];
                    $v['sold_num'] = $scheduleGoodsNum[$v['id']]['sold_num'];
                    $v['schedule_up_count'] = $scheduleGoodsNum[$v['id']]['schedule_up_count'];
                } else {
                    $v['goods_num'] = 0;
                    $v['sold_num'] = 0;
                    $v['schedule_up_count'] = 0;
                }
                $models[$k] = $v;
            }
        }
        return $models;
    }




}