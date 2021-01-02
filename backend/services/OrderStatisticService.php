<?php


namespace backend\services;


use backend\models\constants\DownloadConstants;
use common\models\Common;
use common\models\Customer;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\GoodsSort;
use common\models\Order;
use common\models\OrderGoods;
use common\models\RouteDelivery;
use common\utils\ArrayUtils;
use common\utils\CodeUtils;
use common\utils\DateTimeUtils;
use common\utils\NumberUtils;
use common\utils\StringUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class OrderStatisticService
{

    /**
     * 订单统计-客户维度数据统计
     * @param $bigSort
     * @param $goodsOwner
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadCustomerStatisticData($bigSort, $goodsOwner, $orderTimeStart, $orderTimeEnd, $companyId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable =  Order::tableName();
        $customerTable = Customer::tableName();
        $subConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
        ];
        if (StringUtils::isNotBlank($bigSort)){
            $subConditions["{$orderGoodsTable}.sort_1"] =$bigSort;
        }
        if (StringUtils::isNotBlank($goodsOwner)){
            $subConditions["{$orderTable}.order_owner"] =$goodsOwner;
        }
        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]= $subConditions;

        $customerStatisticList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($customerTable,"{$customerTable}.id={$orderTable}.customer_id")
            ->select([
                "{$customerTable}.nickname as customer_name",
                "{$customerTable}.phone as customer_phone",
                "COUNT(DISTINCT({$orderTable}.order_no)) as order_count",
                "COALESCE(SUM({$orderGoodsTable}.num),0) as order_goods_sum",
                "COUNT(DISTINCT({$orderGoodsTable}.sku_id)) as order_goods_count",
                "COALESCE(SUM({$orderGoodsTable}.amount),0) as order_amount",
            ])->where($conditions)->groupBy(["{$orderTable}.customer_id"]
            )->orderBy("{$orderTable}.customer_id")->all();


        $company = CompanyService::getActiveModel($companyId,false);

        $title = "订单统计-客户维度统计单";
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        //导出
        self::exportCustomerStatisticListExcel($sheet,$nowRow,$company,$customerStatisticList);

        $fileName = $title.DateTimeUtils::parseStandardWLongDate();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
        return;
    }


    /**
     * 订单统计-客户维度数据统计 （EXCEL）
     * @param $sheet
     * @param $nowRow
     * @param $company
     * @param $customerStatisticList
     */
    private static function exportCustomerStatisticListExcel(&$sheet, &$nowRow, $company, $customerStatisticList){

        foreach ($customerStatisticList as $k=> $v){
            $customerStatisticList[$k]['customer_name'] = CodeUtils::rmUTF8UnSupportCharacter($v['customer_name']);
        }
        //导出
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','G',$nowRow++,$company['name']);
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$orderStatisticCustomerStatisticDataList,$customerStatisticList);

    }

    /**
     * 订单统计-团长维度统计单
     * @param $bigSort
     * @param $goodsOwner
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadDeliveryStatisticData($bigSort, $goodsOwner, $orderTimeStart, $orderTimeEnd, $companyId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable =  Order::tableName();
        $deliveryTable = Delivery::tableName();

        $subConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
            // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
        ];
        if (StringUtils::isNotBlank($bigSort)){
            $subConditions["{$orderGoodsTable}.sort_1"] =$bigSort;
        }
        if (StringUtils::isNotBlank($goodsOwner)){
            $subConditions["{$orderTable}.order_owner"] =$goodsOwner;
        }
        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]= $subConditions;

        $deliveryStatisticList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($deliveryTable,"{$deliveryTable}.id={$orderTable}.delivery_id")
            ->select([
                "{$deliveryTable}.nickname as delivery_name",
                "{$deliveryTable}.phone as delivery_phone",
                "{$deliveryTable}.province_id as province_id",
                "{$deliveryTable}.city_id as city_id",
                "{$deliveryTable}.county_id as county_id",
                "{$deliveryTable}.community as delivery_community",
                "{$deliveryTable}.address as delivery_address",
                "COUNT(DISTINCT({$orderTable}.customer_id)) as customer_count",
                "COUNT(DISTINCT({$orderTable}.order_no)) as order_count",
                "COALESCE(SUM({$orderGoodsTable}.num),0) as order_goods_num",
                "COUNT(DISTINCT({$orderGoodsTable}.sku_id)) as order_goods_count",
                "COALESCE(SUM({$orderGoodsTable}.amount),0) as order_amount",
            ])->where($conditions)->groupBy(["{$orderTable}.delivery_id"]
            )->orderBy("{$orderTable}.delivery_id")->all();


        $company = CompanyService::getActiveModel($companyId,false);
        RegionService::batchSetProvinceAndCityAndCounty($deliveryStatisticList);

        $title = "订单统计-团长维度统计单";
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        self::exportDeliveryStatisticListExcel($sheet,$nowRow,$company,$deliveryStatisticList);

        $fileName =$title.DateTimeUtils::parseStandardWLongDate();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
        return;

    }

    /**
     * 订单统计-团长维度统计单 EXCEL
     * @param $sheet
     * @param $nowRow
     * @param $company
     * @param $deliveryStatisticList
     */
    private static function exportDeliveryStatisticListExcel(&$sheet, &$nowRow, $company, $deliveryStatisticList){

        foreach ($deliveryStatisticList as $k=>$v){
            $deliveryStatisticList[$k]['address'] = "{$v['province_text']}{$v['city_text']}{$v['county_text']}{$v['delivery_community']}{$v['delivery_address']}";
        }
        //导出
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','L',$nowRow++,$company['name']);
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$orderStatisticDeliveryStatisticDataList,$deliveryStatisticList);

    }


    /**
     * 订单统计-商品维度统计单
     * @param $bigSort
     * @param $goodsOwner
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     */
    public static function downloadGoodsStatisticData($bigSort, $goodsOwner, $orderTimeStart, $orderTimeEnd, $companyId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable =  Order::tableName();

        $subConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
            // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
        ];
        if (StringUtils::isNotBlank($bigSort)){
            $subConditions["{$orderGoodsTable}.sort_1"] =$bigSort;
        }
        if (StringUtils::isNotBlank($goodsOwner)){
            $subConditions["{$orderTable}.order_owner"] =$goodsOwner;
        }
        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]= $subConditions;

        $goodsStatisticList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->select([
                "{$orderGoodsTable}.schedule_name as schedule_name",
                "{$orderGoodsTable}.goods_name as goods_name",
                "{$orderGoodsTable}.sku_name as sku_name",
                "COALESCE(SUM({$orderGoodsTable}.num),0) as goods_num",
                "{$orderGoodsTable}.purchase_price as purchase_price",
                "{$orderGoodsTable}.sku_price as sku_price",
                "COALESCE(SUM({$orderGoodsTable}.amount),0) as goods_amount",
                "COUNT(DISTINCT({$orderTable}.customer_id)) as customer_count",
                "COUNT(DISTINCT({$orderTable}.delivery_id)) as delivery_count",
            ])->where($conditions)->groupBy(["{$orderGoodsTable}.schedule_id"]
            )->orderBy("{$orderGoodsTable}.schedule_id")->all();


        $company = CompanyService::getActiveModel($companyId,false);

        $title = "订单统计-商品维度统计单";
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        //导出
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow++,$company['name']);
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$orderStatisticGoodsStatisticDataList,$goodsStatisticList);

        $fileName =$title.DateTimeUtils::parseStandardWLongDate();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
        return;

    }




}