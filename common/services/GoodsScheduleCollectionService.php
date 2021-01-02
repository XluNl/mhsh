<?php


namespace common\services;


use common\models\Common;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsScheduleCollection;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;

class GoodsScheduleCollectionService
{
    /**
     * 获取
     * @param $id
     * @param null $company_id
     * @param null $goodsOwnerType
     * @param null $goodsOwnerId
     * @param bool $model
     * @return array|bool|GoodsScheduleCollection|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id,$company_id=null,$goodsOwnerType=null,$goodsOwnerId=null, $model = false){
        $conditions = ['id' => $id,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if (StringUtils::isNotBlank($goodsOwnerType)){
            $conditions['owner_type'] = $goodsOwnerType;
        }
        if (StringUtils::isNotBlank($goodsOwnerId)){
            $conditions['owner_id'] = $goodsOwnerId;
        }
        if ($model){
            return GoodsScheduleCollection::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GoodsScheduleCollection::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    public static function getModels($goodsOwnerType,$goodsOwnerId, $companyId,$name=null,$startTime=null,$endTime=null,$pageNo=1,$pageSize=20){
        $conditions = [
            "and",
            ['status'=>CommonStatus::STATUS_ACTIVE]
        ];
        if (StringUtils::isNotBlank($goodsOwnerType)){
            $conditions[] = ['owner_type'=> $goodsOwnerType];
        }
        if (StringUtils::isNotBlank($goodsOwnerId)){
            $conditions[] = ['owner_id'=> $goodsOwnerId];
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions[] = ['company_id'=> $companyId];
        }
        if (StringUtils::isNotBlank($name)){
            $conditions[] = ['like','collection_name',$name];
        }
        if (StringUtils::isNotBlank($startTime)){
            $conditions[] = ['<=','online_time',$startTime];
        }
        if (StringUtils::isNotBlank($endTime)){
            $conditions[] = ['>=','offline_time',$endTime];
        }
        $query = (new Query())->from(GoodsScheduleCollection::tableName())->where($conditions);
        if ($pageNo!=null&&$pageSize!=null){
            $query = $query->offset($pageSize*($pageNo-1))->limit($pageSize);
        }
        $query->orderBy("id desc");
        return $query->all();
    }

    /**
     * 批量操作状态
     * @param $companyId
     * @param $goodsOwnerType
     * @param $goodsOwnerId
     * @param $collectionId
     * @param $status
     * @return array
     */
    public static function statusOperationP($companyId,$goodsOwnerType,$goodsOwnerId,$collectionId,$status){
        if (!in_array($status,[GoodsConstantEnum::STATUS_UP,GoodsConstantEnum::STATUS_DOWN])){
            return [false,'不支持的操作'];
        }
        GoodsSchedule::updateAll(['schedule_status'=>$status,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],[
            'collection_id'=>$collectionId,
            'company_id'=>$companyId,
            'owner_type'=>$goodsOwnerType,
            'owner_id'=>$goodsOwnerId
        ]);
        return [true,""];
    }

    /**
     * 批量上商品
     * @param $companyId
     * @param $goodsOwnerType
     * @param $goodsOwnerId
     * @param $collectionId
     * @param $goodsData
     * @param $goodsType
     * @param $operationId
     * @param $operationName
     * @return array
     */
    public static function batchAddGoodsP($companyId,$goodsOwnerType,$goodsOwnerId,$collectionId,$goodsData,$goodsType,$operationId,$operationName){
        if (!key_exists($goodsType,GoodsConstantEnum::$scheduleDisplayChannelArr)){
            return [false,'不支持的展示类型'];
        }
        if (!empty($goodsData)){
            $collectionModel = GoodsScheduleCollectionService::getActiveModel($collectionId,$companyId,$goodsOwnerType,$goodsOwnerId,false);
            if (empty($collectionModel)){
                return [false,"排期{$collectionId}不存在"];
            }
            $skuIds = ArrayUtils::getColumnWithoutNull('sku_id',$goodsData);
            $goodsModels = GoodsSkuService::getSkuInfoCommon($skuIds,null,$companyId,$goodsOwnerType,$goodsOwnerId);
            $goodsModels = ArrayUtils::index($goodsModels,'sku_id');
            foreach ($goodsData as $goodsDatum){
                if (key_exists($goodsDatum['sku_id'],$goodsModels)){
                    $skuModel = $goodsModels[$goodsDatum['sku_id']];
                    $scheduleModel = new GoodsSchedule();
                    $scheduleModel->goods_id = $skuModel['goods_id'];
                    $scheduleModel->sku_id = $skuModel['sku_id'];
                    $scheduleModel->price = $skuModel['sale_price'];
                    $scheduleModel->schedule_name = " ";
                    $scheduleModel->schedule_status = GoodsConstantEnum::STATUS_UP;
                    $scheduleModel->schedule_stock = $goodsDatum['stock'];
                    $scheduleModel->schedule_sold = 0;
                    $scheduleModel->schedule_limit_quantity = 9999;
                    $scheduleModel->display_order = 0;
                    $scheduleModel->schedule_display_channel = $goodsType;
                    $scheduleModel->display_start = $collectionModel['display_start'];
                    $scheduleModel->display_end = $collectionModel['display_end'];
                    $scheduleModel->online_time = $collectionModel['online_time'];
                    $scheduleModel->offline_time = $collectionModel['offline_time'];
                    $scheduleModel->expect_arrive_time = $goodsDatum['expect_arrive_time'];
                    $scheduleModel->operation_name = $operationName;
                    $scheduleModel->operation_id = $operationId;
                    $scheduleModel->owner_id = $goodsOwnerId;
                    $scheduleModel->owner_type = $goodsOwnerType;
                    $scheduleModel->company_id = $companyId;
                    $scheduleModel->collection_id = $collectionId;
                    if (!$scheduleModel->save()){
                        return [false,Common::getModelErrors($scheduleModel)];
                    }
                }
                else{
                    return [false,"商品属性{$goodsDatum['sku_id']}不存在"];
                }
            }
            return [true,""];
        }
        else{
            return [false,'添加数据不能为空'];
        }
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
            return null;
        }
        $nowTime = time();
        $onlineTime = strtotime($model['online_time']);
        $offlineTime = strtotime($model['offline_time']);
        if ($nowTime>$offlineTime){
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_END;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_END];
        }
        else if ($model['schedule_up_count']==0){
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_SUSPEND;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_SUSPEND];
        }
        else if ($nowTime<$onlineTime) {
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_WAITING;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_WAITING];
        }
        else{
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_IN_SALE;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_IN_SALE];
        }
        return $model;
    }
}