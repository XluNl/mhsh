<?php


namespace common\services;


use common\models\GoodsSku;
use common\models\GoodsSkuStock;
use common\models\RoleEnum;

class GoodsSkuStockService
{

    /**
     * 批量商品出库
     * @param $companyId
     * @param $num
     * @param $scheduleId
     * @param $skuId
     * @param $goodsId
     * @param $operatorId
     * @param $operatorName
     * @param $operatorRole
     * @return array
     */
    public static function deliveryOutAndLog($companyId,$num,$scheduleId,$skuId,$goodsId,$operatorId,$operatorName,$operatorRole=RoleEnum::ROLE_AGENT){
        if ($num<1){
            return [false,"出库数量至少1个"];
        }
        $stockModel = new GoodsSkuStock();
        $stockModel->schedule_id = $scheduleId;
        $stockModel->sku_id = $skuId;
        $stockModel->num = $num;
        $stockModel->type = GoodsSkuStock::TYPE_GOODS_OUT;
        $stockModel->goods_id = $goodsId;
        $stockModel->company_id = $companyId;
        $stockModel->operator_role = $operatorRole;
        $stockModel->operator_id = $operatorId;
        $stockModel->operator_name = $operatorName;
        $stockModel->remark = "批量出库";
        if (!$stockModel->save()){
            return [false,"排期{$scheduleId}出库记录保持失败"];
        }
        $updateCount = GoodsSku::updateAllCounters(['sku_stock'=>$num],['company_id'=>$stockModel->company_id,'id'=>$stockModel->sku_id]);
        if ($updateCount<1){
            return [false,"属性{$skuId}更新库存失败"];
        }
        return [true,''];
    }
}