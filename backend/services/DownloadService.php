<?php


namespace backend\services;


use backend\models\constants\DownloadConstants;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\models\Common;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\RouteDelivery;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\NumberUtils;
use common\utils\StringUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DownloadService
{
    /**
     * 采购单（小排期）
     * @param $scheduleId
     * @param $companyId
     * @param $validateException
     */
    public static function downloadPurchaseList($scheduleId, $companyId, $validateException){
        $scheduleModel = GoodsScheduleService::requireActiveGoodsSchedule($scheduleId,$companyId,$validateException);
        self::assemblePurchaseList($scheduleId,$companyId);
    }

    /**
     * 采购单（大排期）
     * @param $collectionId
     * @param $companyId
     * @param $validateException
     */
    public static function downloadPurchaseListCollection($collectionId, $companyId, $validateException){
        $scheduleModels = GoodsScheduleService::getActiveGoodsScheduleByCollectionId($collectionId,$companyId,$validateException);
        $scheduleIds = ArrayHelper::getColumn($scheduleModels,'id');
        self::assemblePurchaseList($scheduleIds,$companyId);
    }

    public static function assemblePurchaseList($scheduleIds, $companyId){
        $company = CompanyService::getActiveModel($companyId,false);
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $purchaseDataList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->select([
            "SUM({$orderGoodsTable}.num) as sold_quantity",
            "{$orderGoodsTable}.sort_1",
            "{$orderGoodsTable}.sort_2",
            "{$orderGoodsTable}.goods_id",
            "CONCAT_WS('-',{$orderGoodsTable}.schedule_name,{$orderGoodsTable}.goods_name) as goods_name",
            "{$orderGoodsTable}.sku_id",
            "{$orderGoodsTable}.sku_name",
            "{$orderGoodsTable}.sku_unit",
            "{$orderGoodsTable}.sku_price",
            "{$orderGoodsTable}.schedule_id",
        ])->where([
            'and',
            [
                "{$orderGoodsTable}.schedule_id"=>$scheduleIds,
                "{$orderGoodsTable}.company_id"=>$companyId,
                //"{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
                "{$orderTable}.order_status"=>Order::$downloadStatusArr,
                "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_SELF
            ],
        ])->groupBy(["{$orderGoodsTable}.schedule_id"])
            ->orderBy("{$orderGoodsTable}.sort_1,{$orderGoodsTable}.sort_2,{$orderGoodsTable}.sku_id")->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        if (!empty($purchaseDataList)){
            $skuIds = ArrayHelper::getColumn($purchaseDataList,'sku_id');
            $skuModels = GoodsSkuService::getGoodsSkuList($skuIds,$companyId);
            $skuModels = empty($skuModels)?[]:ArrayHelper::index($skuModels,'id');
            $sortIds = ArrayUtils::getColumnWithoutNull('sort_1',$purchaseDataList);
            $sortIds = array_merge($sortIds,ArrayUtils::getColumnWithoutNull('sort_2',$purchaseDataList));
            $sortModels = GoodsSortService::getActiveModels($sortIds,$companyId);
            $sortModels = empty($sortModels)?[]:ArrayHelper::index($sortModels,'id');
            self::exportPurchaseList($sheet,$nowRow,$company,DateTimeUtils::parseStandardWLongDate(time()),$purchaseDataList,$skuModels,$sortModels);
        }

        $fileName ="采购单-".DateTimeUtils::parseStandardWLongDate(time());
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;
    }

    private static function exportPurchaseList(&$sheet, &$nowRow, $company,$sortingDate,$purchaseDataList,$skuModels,$sortModels){

        $bigSortId = null;
        $smallSortId = null;
        foreach ($purchaseDataList as $k=>$v){
            $v['no'] = $k+1;
            $v['remark'] = '';
            $v['total_amount'] = '';
            $v['franchisees'] = '';
            $v['supplier'] = '';
            $v['big_sort_name'] = '';
            $v['small_sort_name'] = '';

            $v['sku_price'] = Common::showAmount($v['sku_price']);
            if (key_exists($v['sku_id'],$skuModels)){
                $v['stock_quantity'] = $skuModels[$v['sku_id']]['sku_stock'];
            }
            else{
                $v['stock_quantity'] = 0;
            }
            $v['purchase_quantity'] =  $v['sold_quantity']-$v['stock_quantity'];
            $v['purchase_quantity'] = $v['purchase_quantity']<0?0:$v['purchase_quantity'];

            if ($v['sort_1']!=$bigSortId){
                $bigSortId = $v['sort_1'];
                if (key_exists($v['sort_1'],$sortModels)){
                    $v['big_sort_name'] = $sortModels[$v['sort_1']]['sort_name'];
                }
            }
            if ($v['sort_2']!=$smallSortId){
                $smallSortId = $v['sort_2'];
                if (key_exists($v['sort_2'],$sortModels)){
                    $v['small_sort_name'] = $sortModels[$v['sort_2']]['sort_name'];
                }
            }
            $purchaseDataList[$k] = $v;
        }

        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','P',$nowRow++,$company['name']);
        ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'日期');
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','P',$nowRow,$sortingDate);
        $nowRow++;

        self::outputContent($sheet,$nowRow,DownloadConstants::$purchaseList,$purchaseDataList);

        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','B',$nowRow,'采购员签字:');
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'C','M',$nowRow,'');
        ExcelService::setCellValueAndCenter($sheet,'N',$nowRow,'汇总');
        ExcelService::setCellValueAndCenter($sheet,'O',$nowRow,'');
        ExcelService::setCellValueAndCenter($sheet,'P',$nowRow,'');
    }

    /**
     * 分拣单
     * @param $sortingDate
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadSortingList($sortingDate,$orderTimeStart,$orderTimeEnd,$companyId){
        $company = CompanyService::getActiveModel($companyId,false);
        $expectArriveTime = DateTimeUtils::tomorrow($sortingDate);
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();

        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]=[
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
            "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_SELF,
            //"{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
            "{$orderGoodsTable}.company_id"=>$companyId
        ];
        $goodsSkuDataList = (new Query())->from($orderGoodsTable)->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")->select([
            "SUM({$orderGoodsTable}.num) as sold_amount",
            "{$orderGoodsTable}.goods_id",
            "{$orderGoodsTable}.sort_1",
            "CONCAT_WS('-',{$orderGoodsTable}.schedule_name,{$orderGoodsTable}.goods_name) as goods_name",
            "{$orderGoodsTable}.sku_id",
            "{$orderGoodsTable}.sku_name",
            "{$orderGoodsTable}.sku_unit",
            "{$orderGoodsTable}.sku_price",
            "{$orderGoodsTable}.expect_arrive_time",
        ])->where($conditions)->groupBy(['schedule_id']
        )->orderBy('sort_1,sku_id')->all();
        $goodsSkuDataList = empty($goodsSkuDataList)?[]:$goodsSkuDataList;


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("汇总");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        if (!empty($goodsSkuDataList)){
            GoodsSortService::completeSortName($goodsSkuDataList);
            self::exportSorting($sheet,$nowRow,$company,$goodsSkuDataList,$sortingDate);
        }
        $fileName ="{$sortingDate}分拣单-".DateTimeUtils::parseStandardWLongDate();
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;
    }

    private static function exportSorting(&$sheet, &$nowRow, $company, $goodsSkuDataList,$sortingDate){

        $numSum = 0;
        foreach ($goodsSkuDataList as $k=>$v){
            $v['remark'] = '';
            $numSum += $v['sold_amount'];
            $goodsSkuDataList[$k]=$v;
        }

        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow++,$company['name']);

        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','B',$nowRow,'领货时间');
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'C','F',$nowRow,'   月   日   点   分');
        ExcelService::setCellValueAndCenter($sheet,'G',$nowRow,'分拣时间');
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'H','I',$nowRow,$sortingDate);
        $nowRow++;
        self::outputContent($sheet,$nowRow,DownloadConstants::$sortingList,$goodsSkuDataList);

        ExcelService::setCellSign($sheet,"A{$nowRow}","I".($nowRow+2),"              领货人签字：");

    }


    /**
     * 司机路线订单
     * @param $sortingDate
     * @param $orderOwner
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param null $queryRouteId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadRouteSummary($sortingDate,$orderOwner,$orderTimeStart,$orderTimeEnd,$companyId,  $queryRouteId=null){
        $expectArriveTime = DateTimeUtils::tomorrow($sortingDate);
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $routeDeliveryTable = RouteDelivery::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $routeDeliveryConditions = ['company_id'=>$companyId];
        $deliveryDataConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
           // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];
        if (StringUtils::isNotBlank($orderOwner)){
            $deliveryDataConditions["{$orderTable}.order_owner"] = $orderOwner;
        }
        if (NumberUtils::notNullAndPositiveInteger($queryRouteId)){
            $routeDeliveryConditions['route_id'] = $queryRouteId;
            $routeDeliveryModels = (new Query())->from(RouteDelivery::tableName())->where($routeDeliveryConditions)->all();
            if (!empty($routeDeliveryModels)){
                $deliveryIds = ArrayHelper::getColumn($routeDeliveryModels,'delivery_id');
                $deliveryDataConditions["{$orderTable}.delivery_id"] = $deliveryIds;
            }
        }

        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[] = $deliveryDataConditions;

        $deliveryGoodsList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($routeDeliveryTable,"{$orderGoodsTable}.delivery_id={$routeDeliveryTable}.delivery_id")
            ->select([
                "SUM({$orderGoodsTable}.num) as num",
                "{$orderGoodsTable}.delivery_id",
                "{$routeDeliveryTable}.route_id",
            ])->where($conditions)->groupBy(["{$orderGoodsTable}.delivery_id"]
            )->orderBy("{$orderGoodsTable}.delivery_id,{$routeDeliveryTable}.route_id")->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("司机路线订单统计({$sortingDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($deliveryGoodsList)){
            $routeIds = ArrayUtils::getColumnWithoutNull('route_id',$deliveryGoodsList);
            $routeModels = [];
            if (!empty($routeIds)){
                $routeModels = RouteService::getActiveModels($routeIds);
                $routeModels = empty($routeModels)?[]:ArrayHelper::index($routeModels,'id');
            }
            $deliveryIds = ArrayUtils::getColumnWithoutNull('delivery_id',$deliveryGoodsList);
            $deliveryModels = [];
            if (!empty($deliveryIds)){
                $deliveryModels = DeliveryService::getActiveModels($deliveryIds);
                $deliveryModels = empty($deliveryModels)?[]:ArrayHelper::index($deliveryModels,'id');
                RegionService::batchSetProvinceAndCityAndCounty($deliveryModels);
            }
            $routeDataList = [];
            foreach ($deliveryGoodsList as $value){
                if (!key_exists($value['route_id'],$routeDataList)){
                    $routeDataList[$value['route_id']] = [
                        'route_id'=>$value['route_id'],
                        'delivery'=>[]
                    ];
                }
                $routeDataList[$value['route_id']]['delivery'][] = $value;
            }

            self::exportRouteSummary($sheet,$nowRow,$company,$sortingDate,$routeDataList,$deliveryModels,$routeModels);
        }
        $fileName ="司机路线订单({$sortingDate})-".time();
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;

    }


    private static function exportRouteSummary(&$sheet, &$nowRow, $company,$sortingDate, $routeDataList, $deliveryModels, $routeModels){
        foreach ($routeDataList as $routeData){
            $tmpRoute = ['nickname'=>'未知','realname'=>'未知','phone'=>'#','province_text'=>'','city_text'=>'','county_text'=>'','community'=>'','address'=>''];
            if ($routeData['route_id']!==null&&key_exists($routeData['route_id'],$routeModels)){
                $tmpRoute = $routeModels[$routeData['route_id']];
            }
            $numSum = 0;
            foreach ($routeData['delivery'] as $k=>$v){
                $v['no'] = $k+1;
                $v['remark'] = '';
                $numSum += $v['num'];
                if (key_exists($v['delivery_id'],$deliveryModels)){
                    $tmpDelivery = $deliveryModels[$v['delivery_id']];
                    $v['nickname'] = $tmpDelivery['nickname'];
                    $v['realname'] = $tmpDelivery['realname'];
                    $v['address'] = "{{$tmpDelivery['community']}{$tmpDelivery['address']}";
                    $v['phone'] = $tmpDelivery['phone'];
                }
                else{
                    $v['nickname'] = '未知';
                    $v['realname'] = '未知';
                    $v['address'] = '未知';
                    $v['phone'] = '#';
                }
                $routeData['delivery'][$k]=$v;
            }

            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow++,$company['name']);
            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'日期');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','D',$nowRow,$sortingDate);
            ExcelService::setCellValueAndCenter($sheet,'E',$nowRow,'配送员');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'F','I',$nowRow,"{$tmpRoute['nickname']}/{$tmpRoute['phone']}");
            $nowRow++;

            self::outputContent($sheet,$nowRow,DownloadConstants::$routeSummaryList,$routeData['delivery']);

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'总计');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','G',$nowRow,'');
            ExcelService::setCellValueAndCenter($sheet,'H',$nowRow,$numSum);
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,'');
            $nowRow+=5;

        }
    }

    /**
     * 司机送货详单-装车单
     * @param $sortingDate
     * @param $orderOwner
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param null $queryRouteId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadRouteGoods($sortingDate,$orderOwner,$orderTimeStart,$orderTimeEnd,$companyId,  $queryRouteId=null){
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",['order/index']));
        $expectArriveTime = DateTimeUtils::tomorrow($sortingDate);
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $routeDeliveryTable = RouteDelivery::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $deliveryDataConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
           // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
            "{$orderGoodsTable}.company_id"=>$companyId];
        if (StringUtils::isNotBlank($orderOwner)){
            $deliveryDataConditions["{$orderTable}.order_owner"] = $orderOwner;
        }
        if (NumberUtils::notNullAndPositiveInteger($queryRouteId)){
            $routeDeliveryConditions = ['company_id'=>$companyId];
            $routeDeliveryConditions['route_id'] = $queryRouteId;
            $routeDeliveryModels = (new Query())->from(RouteDelivery::tableName())->where($routeDeliveryConditions)->all();
            if (!empty($routeDeliveryModels)){
                $deliveryIds = ArrayHelper::getColumn($routeDeliveryModels,'delivery_id');
                $deliveryDataConditions["{$orderTable}.delivery_id"] = $deliveryIds;
            }
        }

        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]= $deliveryDataConditions;

        $deliveryGoodsList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($routeDeliveryTable,"{$orderGoodsTable}.delivery_id={$routeDeliveryTable}.delivery_id")
            ->select([
                "SUM({$orderGoodsTable}.num) as num",
                "CONCAT_WS('-',{$orderGoodsTable}.schedule_name,{$orderGoodsTable}.goods_name) as goods_name",
                "{$orderGoodsTable}.sku_name",
                "{$orderGoodsTable}.sku_price",
                "{$orderGoodsTable}.sku_unit",
                "{$orderGoodsTable}.expect_arrive_time",
                "{$routeDeliveryTable}.route_id",
            ])->where($conditions)->groupBy(["{$routeDeliveryTable}.route_id","{$orderGoodsTable}.schedule_id"]
            )->orderBy("{$routeDeliveryTable}.route_id,{$orderGoodsTable}.sort_1,{$orderGoodsTable}.sort_2,{$orderGoodsTable}.goods_id,{$orderGoodsTable}.sku_id")->all();


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("司机送货详单-装车单({$sortingDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($deliveryGoodsList)){
            $routeIds = ArrayUtils::getColumnWithoutNull('route_id',$deliveryGoodsList);
            $routeModels = [];
            if (!empty($routeIds)){
                $routeModels = RouteService::getActiveModels($routeIds);
                $routeModels = empty($routeModels)?[]:ArrayHelper::index($routeModels,'id');
            }
            $deliveryDataList = [];
            foreach ($deliveryGoodsList as $value){
                if (!key_exists($value['route_id'],$deliveryDataList)){
                    $deliveryDataList[$value['route_id']] = [
                        'route_id'=>$value['route_id'],
                        'goods'=>[]
                    ];
                }
                $deliveryDataList[$value['route_id']]['goods'][] = $value;
            }
            self::exportRouteGoods($sheet,$nowRow,$company,$deliveryDataList,$routeModels);
        }
        $fileName ="司机送货详单-装车单({$sortingDate})-".time();
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;


    }

    /**
     * 团长订单明细导出
     * @param $sortingDate
     * @param $orderOwner
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadOrderList($sortingDate,$orderOwner,$companyId){
        $expectArriveTime = DateTimeUtils::tomorrow($sortingDate);
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $deliveryTable = Delivery::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $conditions = [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
            // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];
        if (StringUtils::isNotBlank($orderOwner)){
            $conditions["{$orderTable}.order_owner"] = $orderOwner;
        }
        $list = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($deliveryTable,"{$deliveryTable}.id={$orderTable}.delivery_id")
            ->where($conditions)
            ->select([
                "{$orderTable}.accept_name",
                "{$orderTable}.accept_mobile",
                "{$orderTable}.accept_province_id",
                "{$orderTable}.accept_city_id",
                "{$orderTable}.accept_county_id",
                "{$orderTable}.accept_community",
                "{$orderTable}.accept_address",
                "{$orderTable}.delivery_name",
                "{$orderTable}.delivery_id",
                "{$orderTable}.delivery_phone",
                "{$orderGoodsTable}.goods_name",
                "{$orderGoodsTable}.sku_name",
                "{$orderGoodsTable}.num",
                "{$deliveryTable}.nickname as d_nickname",
                "{$deliveryTable}.phone as d_phone",
            ])
            ->orderBy("{$orderTable}.delivery_id,{$orderTable}.customer_id")
            ->all();


        RegionService::batchSetProvinceAndCityAndCountyForOrder($list);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("配送团长订单({$sortingDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($list)){
            $deliveryDataList = [];
            foreach ($list as $value){
                $value['address'] = $value['accept_province_text'].$value['accept_city_text'].$value['accept_county_text'].$value['accept_community'].$value['accept_address'];
                $deliveryDataList[$value['delivery_id']]['d_nickname'] = $value['d_nickname'];
                $deliveryDataList[$value['delivery_id']]['d_phone'] = $value['d_phone'];
                $deliveryDataList[$value['delivery_id']]['goods'][] = $value;
            }
            self::exportOrderListExcel($sheet,$nowRow,$deliveryDataList,$company);
        }

        $fileName ="团长订单明细({$sortingDate})";
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;
    }

    /**
     * 团长订单明细导出excel
     * @param $sheet
     * @param $nowRow
     * @param $deliveryDataList
     * @param $company
     */
    private static function exportOrderListExcel(&$sheet, &$nowRow, $deliveryDataList,$company){
        foreach ($deliveryDataList as $deliveryData){
            foreach ($deliveryData['goods'] as $k=>$v){
                $v['no'] = $k+1;
                $deliveryData['goods'][$k]=$v;
            }

            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','G',$nowRow++,$company['name']);
            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'店长');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','D',$nowRow,$deliveryData['d_nickname']);
            ExcelService::setCellValueAndCenter($sheet,'E',$nowRow,'联系电话');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'F','G',$nowRow,$deliveryData['d_phone']);
            $nowRow++;


            self::outputContent($sheet,$nowRow,DownloadConstants::$orderList,$deliveryData['goods']);
            ExcelService::processRowNo($nowRow);
        }
    }

    private static function exportRouteGoods(&$sheet, &$nowRow, $company, $deliveryDataList, $routeModels){
        foreach ($deliveryDataList as $deliveryData){
            $tmpRoute = ['nickname'=>'未知','realname'=>'未知','phone'=>'#','province_text'=>'','city_text'=>'','county_text'=>'','community'=>'','address'=>''];
            if ($deliveryData['route_id']!==null&&key_exists($deliveryData['route_id'],$routeModels)){
                $tmpRoute = $routeModels[$deliveryData['route_id']];
            }

            $numSum = 0;
            foreach ($deliveryData['goods'] as $k=>$v){
                $v['no'] = $k+1;
                $v['remark'] = '';
                $numSum += $v['num'];
                $deliveryData['goods'][$k]=$v;
            }

            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow++,$company['name']);

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'订货数量');
            ExcelService::setCellValueAndCenter($sheet,'B',$nowRow,$numSum);
            ExcelService::setCellValueAndCenter($sheet,'C',$nowRow,'出库时间');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'D','E',$nowRow,'   月   日   点   分');
            ExcelService::setCellValueAndCenter($sheet,'F',$nowRow,'配送员');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'G','I',$nowRow,"{$tmpRoute['realname']}/{$tmpRoute['phone']}");
            $nowRow++;

            self::outputContent($sheet,$nowRow,DownloadConstants::$routeGoodsList,$deliveryData['goods']);

            ExcelService::setCellSign($sheet,"A{$nowRow}","I".($nowRow+2),"              司机签字：");
            $nowRow+=3;
            //分页打印
            ExcelService::processRowNo($nowRow);
        }
    }


    /**
     * 配送团长接收确认单
     * @param $sortingDate
     * @param $orderOwner
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param null $queryRouteId
     * @param null $queryDeliveryId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadDeliveryGoods($sortingDate,$orderOwner,$orderTimeStart,$orderTimeEnd,$companyId,  $queryRouteId=null, $queryDeliveryId=null){
        $expectArriveTime = DateTimeUtils::tomorrow($sortingDate);
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $routeDeliveryTable = RouteDelivery::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $routeDeliveryConditions = ['company_id'=>$companyId];
        $deliveryDataConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
           // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];
        if (StringUtils::isNotBlank($orderOwner)){
            $deliveryDataConditions["{$orderTable}.order_owner"] = $orderOwner;
        }
        if (NumberUtils::notNullAndPositiveInteger($queryRouteId)){
            $routeDeliveryConditions['route_id'] = $queryRouteId;
            $routeDeliveryModels = (new Query())->from(RouteDelivery::tableName())->where($routeDeliveryConditions)->all();
            if (!empty($routeDeliveryModels)){
                $deliveryIds = ArrayHelper::getColumn($routeDeliveryModels,'delivery_id');
                $deliveryDataConditions["{$orderTable}.delivery_id"] = $deliveryIds;
            }
        }
        else if (NumberUtils::notNullAndPositiveInteger($queryDeliveryId)){
            $deliveryDataConditions["{$orderTable}.delivery_id"] = $queryDeliveryId;
        }

        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]= $deliveryDataConditions;

        $deliveryGoodsList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($routeDeliveryTable,"{$orderGoodsTable}.delivery_id={$routeDeliveryTable}.delivery_id")
            ->select([
            "SUM({$orderGoodsTable}.num) as num",
            "CONCAT_WS('-',{$orderGoodsTable}.schedule_name,{$orderGoodsTable}.goods_name) as goods_name",
            "{$orderGoodsTable}.sku_name",
            "{$orderGoodsTable}.sku_price",
            "{$orderGoodsTable}.sku_unit",
            "{$orderGoodsTable}.delivery_id",
            "{$orderGoodsTable}.expect_arrive_time",
            "{$routeDeliveryTable}.route_id",
        ])->where($conditions)->groupBy(["{$orderGoodsTable}.delivery_id","{$orderGoodsTable}.schedule_id"]
        )->orderBy("{$routeDeliveryTable}.route_id,{$orderGoodsTable}.delivery_id")->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("配送团长接收确认单({$sortingDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($deliveryGoodsList)){
            $routeIds = ArrayUtils::getColumnWithoutNull('route_id',$deliveryGoodsList);
            $routeModels = [];
            if (!empty($routeIds)){
                $routeModels = RouteService::getActiveModels($routeIds);
                $routeModels = empty($routeModels)?[]:ArrayHelper::index($routeModels,'id');
            }
            $deliveryIds = ArrayUtils::getColumnWithoutNull('delivery_id',$deliveryGoodsList);
            $deliveryModels = [];
            if (!empty($deliveryIds)){
                $deliveryModels = DeliveryService::getActiveModels($deliveryIds);
                $deliveryModels = empty($deliveryModels)?[]:ArrayHelper::index($deliveryModels,'id');
                RegionService::batchSetProvinceAndCityAndCounty($deliveryModels);
            }
            $deliveryDataList = [];
            foreach ($deliveryGoodsList as $value){
                if (!key_exists($value['delivery_id'],$deliveryDataList)){
                    $deliveryDataList[$value['delivery_id']] = [
                        'delivery_id'=>$value['delivery_id'],
                        'route_id'=>$value['route_id'],
                        'goods'=>[]
                    ];
                }
                $deliveryDataList[$value['delivery_id']]['goods'][] = $value;
            }

            self::exportDeliveryGoods($sheet,$nowRow,$company,$deliveryDataList,$deliveryModels,$routeModels);
        }
        $fileName ="配送团长接收确认单({$sortingDate})-".time();
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;

    }


    /**
     * 团长订单导出（写EXCEL）
     * @param $sheet
     * @param $nowRow
     * @param $company
     * @param $deliveryDataList
     * @param $deliveryModels
     * @param $routeModels
     */
    private static function exportDeliveryGoods(&$sheet, &$nowRow, $company, $deliveryDataList, $deliveryModels, $routeModels){
        foreach ($deliveryDataList as $deliveryData){
            $tmpRoute = ['nickname'=>'未知','realname'=>'未知','phone'=>'#','province_text'=>'','city_text'=>'','county_text'=>'','community'=>'','address'=>''];
            if ($deliveryData['route_id']!==null&&key_exists($deliveryData['route_id'],$routeModels)){
                $tmpRoute = $routeModels[$deliveryData['route_id']];
            }
            $tmpDelivery = ['nickname'=>'未知','realname'=>'未知','community'=>'未知','phone'=>'#','type'=>0];
            if ($deliveryData['delivery_id']!==null&&key_exists($deliveryData['delivery_id'],$deliveryModels)){
                $tmpDelivery = $deliveryModels[$deliveryData['delivery_id']];
            }


            $needAmountSum = 0;
            $numSum = 0;
            foreach ($deliveryData['goods'] as $k=>$v){
                $v['no'] = $k+1;
                $v['need_amount'] = $v['num'] * $v['sku_price'];
                $needAmountSum += $v['need_amount'];
                $numSum += $v['num'];
                $deliveryData['goods'][$k]=$v;
            }

            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow++,$company['name']);
            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'团长名');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','C',$nowRow,$tmpDelivery['nickname']);
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'D','E',$nowRow,'姓名/联系方式');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'F','G',$nowRow,"{$tmpDelivery['nickname']}/{$tmpDelivery['phone']}");
            ExcelService::setCellValueAndCenter($sheet,'H',$nowRow,'团长类型');
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,ArrayUtils::getArrayValue($tmpDelivery['type'],Delivery::$typeArr));
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'详细地址');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','I',$nowRow,"{$tmpDelivery['province_text']}{$tmpDelivery['city_text']}{$tmpDelivery['county_text']}{$tmpDelivery['community']}{$tmpDelivery['address']}");
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'订货数量');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','E',$nowRow,$numSum);
            ExcelService::setCellValueAndCenter($sheet,'F',$nowRow,'配送员');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'G','I',$nowRow,"{$tmpRoute['realname']}/{$tmpRoute['phone']}");
            $nowRow++;

            self::outputContent($sheet,$nowRow,DownloadConstants::$deliveryGoodsList,$deliveryData['goods']);

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'总计');
            ExcelService::mergeCellOneRow($sheet,'B','H',$nowRow);
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,Common::showAmount($needAmountSum));
            $nowRow++;


            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow,"公司售后联系方式:{$company['service_phone']},微信客服：满好生活");
            $nowRow++;
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow,"收到产品请及时检查，若有问题马上反馈，谢谢");
            ExcelService::boldCellOneRow($sheet,'A','J',$nowRow);
            $nowRow++;
            ExcelService::setCellSign($sheet,"A{$nowRow}","I".($nowRow+2),"              收货人签字：");
            $nowRow+=3;
            //分页打印
            ExcelService::processRowNo($nowRow);
        }
    }


    /**
     * 团长订单下载
     * @param $date
     * @param $companyId
     * @param null $queryDeliveryId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadDeliveryOrder($date,$companyId, $queryDeliveryId=null){
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($date),RedirectParams::create("时间格式错误：{$date}",['delivery/delivery-map']));
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime =DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));;
        $orderTable = Order::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $deliveryOrderDataConditions= [
            'and',
            [
                "{$orderTable}.company_id"=>$companyId,
                "{$orderTable}.order_status"=>Order::$downloadStatusArr,
                "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_SELF,
            ],
            ['between',"{$orderTable}.created_at",$startTime,$endTime]
        ];
        if (NumberUtils::notNullAndPositiveInteger($queryDeliveryId)){
            $deliveryOrderDataConditions[] = ["{$orderTable}.delivery_id"=>$queryDeliveryId];
        }
        $deliveryOrderDataList = Order::find()->where($deliveryOrderDataConditions)->with(['goods'])
            ->orderBy("{$orderTable}.delivery_id")->asArray()->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("配送团长订单({$date})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($deliveryOrderDataList)){
            RegionService::batchSetProvinceAndCityAndCountyForOrder($deliveryOrderDataList);
            self::exportOrderExcel($sheet,$nowRow,$company,$deliveryOrderDataList);
        }
        $fileName ="配送团长订单({$date})-".time();
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;

    }


    /**
     * 单独导出订单
     * @param $orderNo
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadOrder($orderNo,$companyId){

        $order = OrderService::getOrderDetail($orderNo,$companyId,true,RedirectParams::create("订单不存在",['order/index']),false);
        $company = CompanyService::getActiveModel($companyId,false);
        RegionService::setProvinceAndCityAndCountyForOrder($order);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($orderNo);
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        self::exportOrderExcel($sheet,$nowRow,$company,[$order]);

        $fileName ="订单{$orderNo}-".DateTimeUtils::parseStandardWLongDate();
        self::outputToBrowser($fileName,$writer,$spreadsheet);
        return;
    }



    /**
     * 完整订单(写入excel)
     * @param $sheet
     * @param $nowRow
     * @param $company
     * @param $deliveryOrderDataList
     */
    private static function exportOrderExcel(&$sheet, &$nowRow, $company, $deliveryOrderDataList){
        foreach ($deliveryOrderDataList as $order){
            $orderGoodsList = $order['goods'];

            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow++,$company['name']);
            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'用户名');
            ExcelService::setCellValueAndCenter($sheet,'B',$nowRow,$order['accept_nickname']);
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'C','D',$nowRow,'姓名/联系方式');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'E','G',$nowRow,"{$order['accept_name']}/{$order['accept_mobile']}");
            ExcelService::setCellValueAndCenter($sheet,'H',$nowRow,'送货方式');
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,ArrayUtils::getArrayValue($order['accept_delivery_type'],GoodsConstantEnum::$deliveryTypeArr));
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'详细地址');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','I',$nowRow,"{$order['accept_province_text']}{$order['accept_city_text']}{$order['accept_county_text']}{$order['accept_community']}{$order['accept_address']}");
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'订单号');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','F',$nowRow,$order['order_no']);
            ExcelService::setCellValueAndCenter($sheet,'G',$nowRow,'配送员');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'H','I',$nowRow,"{$order['delivery_name']}/{$order['delivery_phone']}");
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'用户留言');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','I',$nowRow,$order['order_note']);
            $nowRow++;


            foreach ($orderGoodsList as $k=>$v){
                $v['no'] = $k+1;
                $v['need_amount'] = $v['num'] *$v['sku_price'];
                $orderGoodsList[$k]=$v;
            }
            self::outputContent($sheet,$nowRow,DownloadConstants::$orderGoodsList,$orderGoodsList);


            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'总计');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','H',$nowRow,'');
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,Common::showAmount($order['need_amount']));
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'配送费');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','H',$nowRow,'');
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,Common::showAmount($order['freight_amount']));
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'优惠额');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','H',$nowRow,'');
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,Common::showAmount($order['discount_amount']));
            $nowRow++;

            ExcelService::setCellValueAndCenter($sheet,'A',$nowRow,'已支付金额');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'B','H',$nowRow,'');
            ExcelService::setCellValueAndCenter($sheet,'I',$nowRow,Common::showAmount($order['pay_amount']));
            $nowRow++;

            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow,"团长联系方式:{$order['delivery_phone']},微信客服：满好生活");
            $nowRow++;
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','I',$nowRow,"收到产品请及时检查，若有问题马上反馈，谢谢");
            ExcelService::boldCellOneRow($sheet,'A','I',$nowRow);
            $nowRow+=5;

        }
    }


    /**
     * 导出excelHeader
     * @param $sheet
     * @param $nowRow
     * @param $headersMap
     */
    public static function outputHeader(&$sheet, &$nowRow, $headersMap){
        if (empty($headersMap)){
            return;
        }
        $column = 'A';
        foreach ($headersMap as $v){
            $cols = ArrayUtils::getArrayValue('cols',$v,1);
            if ($cols>1){
                ExcelService::mergeCellOneRow($sheet,$column,ExcelService::addColNum($column,$cols-1),$nowRow);
                ExcelService::borderCenterOneRow($sheet,$column,ExcelService::addColNum($column,$cols-1),$nowRow);
            }
            ExcelService::setCellValueAndCenter($sheet,$column,$nowRow,$v['title']);
            $column = ExcelService::addColNum($column,$cols);
        }
        $nowRow++;
    }


    /**
     *
     * @param $sheet Worksheet
     * @param $nowRow
     * @param $headersMap
     * @param $rowDataList
     */
    public static function outputContent(&$sheet, &$nowRow, $headersMap, $rowDataList){
        if (empty($headersMap)||empty($rowDataList)){
            return;
        }
        $column = 'A';
        foreach ($headersMap as $v){
            $cols = ArrayUtils::getArrayValue('cols',$v,1);
            if ($cols>1){
                ExcelService::mergeCellOneRow($sheet,$column,ExcelService::addColNum($column,$cols-1),$nowRow);
                ExcelService::borderCenterOneRow($sheet,$column,ExcelService::addColNum($column,$cols-1),$nowRow);
            }
            ExcelService::setCellValueAndCenter($sheet,$column,$nowRow,$v['title']);
            $column = ExcelService::addColNum($column,$cols);
        }
        $nowRow++;
        foreach ($rowDataList as $rowK=> $rowV){
            $column = 'A';
            foreach ($headersMap as $headerK =>$headerV){
                $cols = ArrayUtils::getArrayValue('cols',$headerV,1);
                if ($cols>1){
                    ExcelService::mergeCellOneRow($sheet,$column,ExcelService::addColNum($column,$cols-1),$nowRow);
                    ExcelService::borderCenterOneRow($sheet,$column,ExcelService::addColNum($column,$cols-1),$nowRow);
                }
                if (ArrayUtils::getArrayValue('type',$headerV)==DownloadConstants::CELL_TYPE_MONEY){
                    ExcelService::setCellValueAndCenter($sheet,$column,$nowRow, Common::showAmount($rowV[$headerK]));
                }
                else if (ArrayUtils::getArrayValue('type',$headerV)==DownloadConstants::CELL_TYPE_MONEY_WITH_YUAN){
                    ExcelService::setCellValueAndCenter($sheet,$column,$nowRow,Common::showAmountWithYuan($rowV[$headerK]));
                }
                else if (ArrayUtils::getArrayValue('type',$headerV)==DownloadConstants::CELL_TYPE_PERCENTAGE){
                    ExcelService::setCellValueAndCenter($sheet,$column,$nowRow,$rowV[$headerK]*100);
                }
                else if (ArrayUtils::getArrayValue('type',$headerV)==DownloadConstants::CELL_TYPE_PERCENTAGE_WITH_SYMBOL){
                    ExcelService::setCellValueAndCenter($sheet,$column,$nowRow,($rowV[$headerK]*100).'%');
                }
                else{
                    ExcelService::setCellValueAndCenter($sheet,$column,$nowRow,$rowV[$headerK]);
                }
                $column = ExcelService::addColNum($column,$cols);
            }
            $nowRow++;
        }

    }

    /**
     * @param $fileName
     * @param $writer
     * @param $spreadsheet Spreadsheet
     */
    public static function outputToBrowser($fileName,$writer,$spreadsheet){
        header("Content-type: text/html; charset=utf-8");
        $fileName = iconv('utf-8','gb2312',$fileName);
        header("Content-Type:application/vnd.ms-excel");//告诉浏览器将要输出Excel03版本文件
        header("Content-Disposition: attachment;filename=".$fileName.'.xls');//告诉浏览器输出浏览器名称
        header("Cache-Control: max-age=0");//禁止缓存
        $writer->save("php://output");
        //删除清空：
        //删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit();
    }

}