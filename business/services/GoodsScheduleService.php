<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:10
 */

namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;

class GoodsScheduleService extends \common\services\GoodsScheduleService
{


    public static function getFilterList($deliveryModel,$collectionId,$pageNo=1,$pageSize=20){
        $conditions = [
            "and",
            [
                'collection_id'=>$collectionId,
                'owner_type'=>GoodsConstantEnum::OWNER_DELIVERY,
                'owner_id'=>$deliveryModel['id'],
                'company_id'=>$deliveryModel['company_id'],
            ]
        ];
        $query= GoodsSchedule::find()->where($conditions)->with(['goods','goodsSku']);
        if ($pageNo!=null&&$pageSize!=null){
            $query = $query->offset($pageSize*($pageNo-1))->limit($pageSize);
        }
        $scheduleModels = $query->orderBy("schedule_display_channel asc,expect_arrive_time asc,id desc")->asArray()->all();
        $scheduleModels = self::renameImagesAndText($scheduleModels);
        GoodsSortService::completeSortNameWithSub($scheduleModels,'goods');
        $res = [];
        foreach ($scheduleModels as $k=>$v){
            if (!key_exists($v['schedule_display_channel'],$res)){
                $res[$v['schedule_display_channel']] = [
                    'schedules'=>[],
                    'schedule_display_channel'=>$v['schedule_display_channel'],
                    'schedule_display_channel_text'=>ArrayUtils::getArrayValue($v['schedule_display_channel'],GoodsConstantEnum::$scheduleDisplayChannelArr),
                ];
            }
            $res[$v['schedule_display_channel']]['schedules'][] = $v;
        }
        return array_values($res);
    }


    /**
     * 设置预售数量
     * @param $deliveryModel
     * @param $scheduleId
     * @param $stock
     */
    public static function setStock($deliveryModel,$scheduleId,$stock){
        ExceptionAssert::assertTrue($stock>=0,StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_MODIFY_STOCK_ERROR,'库存不能设置为负数'));
        $update = GoodsSchedule::updateAll(['schedule_stock'=>$stock,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],[
            'id'=>$scheduleId,
            'owner_id'=>$deliveryModel['id'],
            'owner_type'=>GoodsConstantEnum::OWNER_DELIVERY,
            'company_id'=>$deliveryModel['company_id']
        ]);
        ExceptionAssert::assertTrue($update>0,StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_MODIFY_STOCK_ERROR,''));

    }

    /**
     * 修改状态
     * @param $deliveryModel
     * @param $id
     * @param $status
     */
    public static function statusOperation($deliveryModel,$id,$status){
        ExceptionAssert::assertTrue(in_array($status,[GoodsConstantEnum::STATUS_UP,GoodsConstantEnum::STATUS_DOWN]),StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_COLLECTION_STATUS_OPERATION_ERROR,'不支持的操作'));
        list($result,$errMsg) = parent::statusOperationP($deliveryModel['company_id'],GoodsConstantEnum::OWNER_DELIVERY,$deliveryModel['id'],$id,$status);
        ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_COLLECTION_STATUS_OPERATION_ERROR,$errMsg));
    }

    /**
     * 根据排期id批量获取（带商品&属性）
     * @param $scheduleIds
     * @param $company_id
     * @param $deliveryId
     * @return array
     */
    public static function getActiveGoodsScheduleWithGoodsAndSkuB($scheduleIds, $company_id, $deliveryId){
        return parent::getActiveGoodsScheduleWithGoodsAndSku($scheduleIds, $company_id,GoodsConstantEnum::OWNER_DELIVERY, $deliveryId);
    }


    /**
     * 修改排期商品里的图片和状态文本
     * @param $scheduleModels
     * @return mixed
     */
    public static function renameImagesAndText($scheduleModels)
    {
        if (!empty($scheduleModels)) {
            foreach ($scheduleModels as $k => $v) {
                if (key_exists('goods', $v)) {
                    $v['goods'] = GoodsDisplayDomainService::renameImageUrl($v['goods'], 'goods_img');
                    $v['goods']['goods_status_text'] = ArrayUtils::getArrayValue($v['goods']['goods_status'],GoodsConstantEnum::$statusArr);
                }
                if (key_exists('goodsSku', $v)) {
                    $v['goodsSku'] = GoodsDisplayDomainService::renameImageUrl($v['goodsSku'], 'sku_img');
                    $v['goodsSku']['sku_status_text'] = ArrayUtils::getArrayValue($v['goodsSku']['sku_status'],GoodsConstantEnum::$statusArr);
                }
                $v['schedule_status_text'] = ArrayUtils::getArrayValue($v['schedule_status'],GoodsConstantEnum::$statusArr);
                $v['schedule_display_channel_text'] = ArrayUtils::getArrayValue($v['schedule_display_channel'],GoodsConstantEnum::$scheduleDisplayChannelArr);
                $scheduleModels[$k] = $v;
            }
        }
        return $scheduleModels;
    }
}