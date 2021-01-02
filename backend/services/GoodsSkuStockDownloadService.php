<?php


namespace backend\services;

use backend\models\constants\DownloadConstants;
use common\models\Common;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\models\GoodsSkuStock;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use yii\db\Query;

class GoodsSkuStockDownloadService
{

    /**
     * 导出出库日志
     * @param $query
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function exportLog($query, $companyId){
        $stockTable = GoodsSkuStock::tableName();
        $goodsSkuTable = GoodsSku::tableName();
        $goodsTable = Goods::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $query->leftJoin($goodsTable,"{$stockTable}.goods_id={$goodsTable}.id");
        $query->leftJoin($goodsSkuTable,"{$stockTable}.sku_id={$goodsSkuTable}.id");
        $query->select("{$stockTable}.*,{$goodsTable}.goods_name,{$goodsSkuTable}.sku_name");
        $query->with = [];
        $query->orderBy("{$stockTable}.id")->asArray();
        $stockLogList = $query->all();
        $nowDate = DateTimeUtils::parseChineseDateTime(time(),false);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("出入库记录({$nowDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($stockLogList)){
            self::writeExcelStockLog($sheet,$nowRow,$stockLogList,$company);
        }
        $fileName ="出入库记录-".time();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
    }


    /**
     * 导出出库日志（写excel）
     * @param $sheet
     * @param $nowRow
     * @param $stockLogList
     * @param $company
     */
    private static function writeExcelStockLog(&$sheet, &$nowRow,$stockLogList,$company){
        foreach ($stockLogList as $k=>$v) {
            $v['type'] = ArrayUtils::getArrayValue($v['type'],GoodsSkuStock::$typeArr);
            $stockLogList[$k]= $v;
        }
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','J',$nowRow++,$company['name']);
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$goodsSkuStockLogList,$stockLogList);
    }



    /**
     * 导出商品库存
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function exportGoodsStock($companyId){

        $goodsSkuTable = GoodsSku::tableName();
        $goodsTable = Goods::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $query = (new Query())->from($goodsTable);
        $query->leftJoin($goodsSkuTable,"{$goodsSkuTable}.goods_id={$goodsTable}.id");
        $query->select("{$goodsTable}.*,{$goodsSkuTable}.*,{$goodsTable}.display_order as goods_display_order,{$goodsSkuTable}.display_order as sku_display_order,{$goodsTable}.id as goods_id");
        $query->andFilterWhere([
            'and',
            ["<>","{$goodsSkuTable}.sku_stock",0],
            ["{$goodsTable}.goods_status"=>GoodsConstantEnum::$activeStatusArr],
            ["{$goodsSkuTable}.sku_status"=>GoodsConstantEnum::$activeStatusArr],
        ]);
        $query->orderBy("sort_1,sort_2");
        $goodsList = $query->all();
        GoodsSortService::completeSortName($goodsList);
        $nowDate = DateTimeUtils::parseChineseDateTime(time(),false);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("商品库存导出单({$nowDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($goodsList)){
            self::writeExcelGoodsStock($sheet,$nowRow,$goodsList,$company);
        }
        $fileName ="商品库存导出单-".time();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
    }

    /**
     * 导出商品库存（写excel）
     * @param $sheet
     * @param $nowRow
     * @param $goodsList
     * @param $company
     */
    private static function writeExcelGoodsStock(&$sheet, &$nowRow, $goodsList,$company){
        $goodOwner = null;
        $bigSortId = null;
        $smallSortId = null;
        $goodsId = null;
        foreach ($goodsList as $k=>$v) {
            if ($goodOwner!=$v['goods_owner']){
                $goodOwner = $v['goods_owner'];
                $v['goods_owner_name'] = ArrayUtils::getArrayValue($goodOwner,GoodsConstantEnum::$ownerArr);
            }
            else{
                $v['goods_owner_name'] = "";
            }

            if ($bigSortId!=$v['sort_1']){
                $bigSortId = $v['sort_1'];
            }
            else{
                $v['sort_1_name'] = "";
            }

            if ($smallSortId!=$v['sort_2']){
                $smallSortId = $v['sort_2'];
            }
            else{
                $v['sort_2_name'] = "";
            }

            if ($goodsId!=$v['goods_id']){
                $goodsId = $v['goods_id'];
                $v['goods_type'] = ArrayUtils::getArrayValue($v['goods_type'],GoodsConstantEnum::$typeArr);
                $v['goods_status'] = ArrayUtils::getArrayValue($v['goods_status'],GoodsConstantEnum::$statusArr);
            }
            else{
                $v['goods_name'] = "";
                $v['goods_type'] = "";
                $v['goods_status'] = "";
                $v['goods_display_order'] = "";
            }

            if (!empty($v['sku_name'])){
                $v['sku_status'] = ArrayUtils::getArrayValue($v['sku_status'],GoodsConstantEnum::$statusArr);
                $v['sku_standard'] = ArrayUtils::getArrayValue($v['sku_standard'],GoodsSku::$skuStandardArr);
                $v['purchase_price'] = Common::showAmount($v['purchase_price']);
                $v['reference_price'] = Common::showAmount($v['reference_price']);
                $v['one_level_rate'] = Common::showPercent($v['one_level_rate']);
                $v['two_level_rate'] = Common::showPercent($v['two_level_rate']);
                $v['share_rate_1'] = Common::showPercent($v['share_rate_1']);
                $v['delivery_rate'] = Common::showPercent($v['delivery_rate']);
            }
            $goodsList[$k]= $v;
        }
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','Y',$nowRow++,$company['name']);
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$goodsSkuList,$goodsList);
    }
}