<?php


namespace backend\services;


use backend\models\constants\DownloadConstants;
use common\models\Common;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\GoodsSort;
use common\models\Order;
use common\models\OrderGoods;
use common\models\RouteDelivery;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\NumberUtils;
use common\utils\StringUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class SortDownloadService
{

    /**
     * 分类分拣单
     * @param $bigSort
     * @param $goodsOwner
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadSortCollectionList($bigSort, $goodsOwner, $expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable =  Order::tableName();
        $goodsSortTable = GoodsSort::tableName();

        $sortCollectionConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
            // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];
        if (StringUtils::isNotBlank($bigSort)){
            $sortCollectionConditions["{$orderGoodsTable}.sort_1"] =$bigSort;
        }
        if (StringUtils::isNotBlank($goodsOwner)){
            $sortCollectionConditions["{$orderTable}.order_owner"] =$goodsOwner;
        }
        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]= $sortCollectionConditions;

        $orderSortGoodsList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($goodsSortTable,"{$goodsSortTable}.id={$orderGoodsTable}.sort_1")
            ->select([
                "SUM({$orderGoodsTable}.num) as num",
                "CONCAT_WS('-',{$orderGoodsTable}.schedule_name,{$orderGoodsTable}.goods_name) as goods_name",
                "{$orderGoodsTable}.sku_name",
                "{$orderGoodsTable}.sku_price",
                "{$orderGoodsTable}.sku_unit",
                "{$goodsSortTable}.sort_name as big_sort_name",
            ])->where($conditions)->groupBy(["{$orderGoodsTable}.schedule_id"]
            )->orderBy("{$orderGoodsTable}.goods_owner,{$orderGoodsTable}.sort_1,{$orderGoodsTable}.sort_2,{$orderGoodsTable}.goods_id,{$orderGoodsTable}.sku_id")->all();


        $company = CompanyService::getActiveModel($companyId,false);

        RegionService::setProvinceAndCityAndCountyForOrder($order);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("分类-分拣单");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        self::exportSortCollectionListExcel($sheet,$nowRow,$company,$orderSortGoodsList);

        $fileName ="分类-分拣单".DateTimeUtils::parseStandardWLongDate();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
        return;
    }


    /**
     * 分类分拣单
     * @param $sheet
     * @param $nowRow
     * @param $company
     * @param $orderSortGoodsList
     */
    private static function exportSortCollectionListExcel(&$sheet, &$nowRow, $company, $orderSortGoodsList){
        ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','H',$nowRow++,$company['name']);
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$sortCollectionOrderList,$orderSortGoodsList);
    }

    /**
     * 分类-明细单
     * @param $bigSort
     * @param $goodsOwner
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadSortDetailList($bigSort, $goodsOwner, $expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable =  Order::tableName();
        $goodsSortTable = GoodsSort::tableName();
        $deliveryTable = Delivery::tableName();

        $sortDetailConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
            // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];
        if (StringUtils::isNotBlank($bigSort)){
            $sortDetailConditions["{$orderGoodsTable}.sort_1"] =$bigSort;
        }
        if (StringUtils::isNotBlank($goodsOwner)){
            $sortDetailConditions["{$orderTable}.order_owner"] =$goodsOwner;
        }
        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        $conditions[]= $sortDetailConditions;

        $orderSortDetailList = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($goodsSortTable,"{$goodsSortTable}.id={$orderGoodsTable}.sort_1")
            ->leftJoin($deliveryTable,"{$deliveryTable}.id={$orderTable}.delivery_id")
            ->select([
                "SUM({$orderGoodsTable}.num) as num",
                "CONCAT_WS('-',{$orderGoodsTable}.schedule_name,{$orderGoodsTable}.goods_name) as goods_name",
                "{$orderGoodsTable}.sku_name",
                "{$goodsSortTable}.id as big_sort_id",
                "{$goodsSortTable}.sort_name as big_sort_name",
                "{$orderTable}.accept_nickname",
                "{$orderTable}.accept_mobile",
                "{$orderTable}.accept_province_id",
                "{$orderTable}.accept_city_id",
                "{$orderTable}.accept_county_id",
                "{$orderTable}.accept_community",
                "{$orderTable}.accept_address",
            ])->where($conditions)->groupBy(["{$orderTable}.customer_id,{$orderGoodsTable}.schedule_id"]
            )->orderBy("{$orderGoodsTable}.goods_owner,{$orderGoodsTable}.sort_1,{$orderGoodsTable}.sort_2,{$orderTable}.delivery_id,{$orderTable}.customer_id,{$orderGoodsTable}.goods_id,{$orderGoodsTable}.sku_id")->all();


        $company = CompanyService::getActiveModel($companyId,false);
        RegionService::batchSetProvinceAndCityAndCountyForOrder($orderSortDetailList);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("分类-明细单");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;

        self::exportSortDetailListExcel($sheet,$nowRow,$company,$orderSortDetailList);

        $fileName ="分类-明细单".DateTimeUtils::parseStandardWLongDate();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
        return;

    }

    /**
     * 分类-明细单EXCEL
     * @param $sheet
     * @param $nowRow
     * @param $company
     * @param $orderSortDetailList
     */
    private static function exportSortDetailListExcel(&$sheet, &$nowRow, $company, $orderSortDetailList){
        $orderSortDetailArrayList = [];
        $tmpSortId = null;
        $i = null;
        foreach ($orderSortDetailList as $v){
            if ($v['big_sort_id']!=$tmpSortId){
                $orderSortDetailArrayList[$v['big_sort_id']] = ['big_sort_name'=>$v['big_sort_name']];
                $orderSortDetailArrayList[$v['big_sort_id']]['goods'] = [];
                $i=1;
                $tmpSortId = $v['big_sort_id'];
            }
            $orderSortDetailArrayList[$v['big_sort_id']]['goods'][] =[
                'id'=>$i++,
                'name_and_phone'=>"{$v['accept_nickname']}/{$v['accept_mobile']}",
                'address'=>"{$v['accept_province_text']}{$v['accept_city_text']}{$v['accept_county_text']}{$v['accept_community']}{$v['accept_address']}",
                'goods_name'=>$v['goods_name'],
                'sku_name'=>$v['sku_name'],
                'num'=>$v['num'],
            ];
        }
        foreach ($orderSortDetailArrayList as $v){
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','M',$nowRow++,$company['name']);
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'A','C',$nowRow,'一级分类');
            ExcelService::setCellValueAndMergeCellAndCenter($sheet,'D','M',$nowRow++,$v['big_sort_name']);
            DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$sortDetailOrderList,$v['goods']);
            $nowRow+=3;
        }
    }

    /**
     * 分类-配送团长接收确认单
     * @param $bigSort
     * @param $goodsOwner
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param null $queryRouteId
     * @param null $queryDeliveryId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function downloadSortDeliveryGoods($bigSort, $goodsOwner,$expectArriveTime,$orderTimeStart,$orderTimeEnd,$companyId, $queryRouteId=null, $queryDeliveryId=null){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $routeDeliveryTable = RouteDelivery::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $routeDeliveryConditions = ['company_id'=>$companyId];
        $deliveryDataConditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$downloadStatusArr,
            "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_SELF,
            // "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];
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

        if (StringUtils::isNotBlank($bigSort)){
            $sortDetailConditions["{$orderGoodsTable}.sort_1"] =$bigSort;
        }
        if (StringUtils::isNotBlank($goodsOwner)){
            $sortDetailConditions["{$orderTable}.order_owner"] =$goodsOwner;
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
        $sheet->setTitle("分类-配送团长接收确认单");
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
        $fileName ="分类-配送团长接收确认单-".time();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
        return;

    }

    /**
     * 分类-配送团长接收确认单（写EXCEL）
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

            DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$deliveryGoodsList,$deliveryData['goods']);

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



}