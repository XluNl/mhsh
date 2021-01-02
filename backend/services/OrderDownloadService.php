<?php


namespace backend\services;


use backend\models\constants\DownloadConstants;
use common\models\Order;
use common\models\OrderGoods;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use yii\db\ActiveQuery;

class OrderDownloadService
{
    /**
     * 导出订单
     * @param $query ActiveQuery
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function exportOrder($query, $companyId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $query->leftJoin($orderGoodsTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no");
        $query->select("{$orderTable}.*,{$orderGoodsTable}.*,{$orderGoodsTable}.sku_price as order_goods_sku_price,{$orderGoodsTable}.num as order_goods_num,{$orderGoodsTable}.discount as order_goods_discount_amount,{$orderGoodsTable}.amount as order_goods_amount");
        $query->with = [];
        $query->orderBy("{$orderTable}.created_at,{$orderTable}.order_no")->asArray();
        $orderGoodsList = $query->all();
        RegionService::batchSetProvinceAndCityAndCountyForOrder($orderGoodsList);

        $nowDate = DateTimeUtils::parseChineseDateTime(time(),false);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("商品导出单({$nowDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($orderGoodsList)){
            self::writeExcelGoods($sheet,$nowRow,$orderGoodsList);
        }
        $fileName ="订单自定义导出-".time();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
    }

    /**
     * 导出订单（写excel）
     * @param $sheet
     * @param $nowRow
     * @param $orderGoodsList
     */
    private static function writeExcelGoods(&$sheet, &$nowRow, $orderGoodsList){
        foreach ($orderGoodsList as $k=> $v){
            $v['pay_status'] = ArrayUtils::getArrayValue($v['pay_status'],Order::$pay_status_list);
            $v['accept_address'] = $v['accept_province_text'].$v['accept_city_text'].$v['accept_county_text'].$v['accept_community'].$v['accept_address'];
            $orderGoodsList[$k] = $v;
        }
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$orderListExport,$orderGoodsList);
    }

}