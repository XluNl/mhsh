<?php


namespace backend\services;


use backend\models\constants\DownloadConstants;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use common\models\Common;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use yii\db\ActiveQuery;

class GoodsDownloadService
{
    /**
     * 导出商品
     * @param $query ActiveQuery
     * @param $companyId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function exportGoods($query, $companyId){
        $goodsSkuTable = GoodsSku::tableName();
        $goodsTable = Goods::tableName();
        $company = CompanyService::getActiveModel($companyId,false);
        $query->leftJoin($goodsSkuTable,"{$goodsSkuTable}.goods_id={$goodsTable}.id");
        $query->select("{$goodsTable}.*,{$goodsSkuTable}.*,{$goodsTable}.display_order as goods_display_order,{$goodsSkuTable}.display_order as sku_display_order,{$goodsTable}.id as goods_id");
        $query->with = [];
        $query->andFilterWhere([
            'and',
            ["{$goodsTable}.goods_status"=>GoodsConstantEnum::$activeStatusArr],
            [
                'or',
                ["{$goodsSkuTable}.sku_status"=>GoodsConstantEnum::$activeStatusArr],
                "{$goodsSkuTable}.sku_status IS NULL",
            ]
        ]);
        $query->orderBy("sort_1,sort_2")->asArray();
        $goodsList = $query->all();
        GoodsSortService::completeSortName($goodsList);
        $nowDate = DateTimeUtils::parseChineseDateTime(time(),false);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("商品导出单({$nowDate})");
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        if (!empty($goodsList)){
            self::writeExcelGoods($sheet,$nowRow,$goodsList);
        }
        $fileName ="商品导出单-".time();
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
    }

    /**
     * 导出商品（写excel）
     * @param $sheet
     * @param $nowRow
     * @param $goodsList
     */
    private static function writeExcelGoods(&$sheet, &$nowRow,$goodsList){
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
        DownloadService::outputContent($sheet,$nowRow,DownloadConstants::$goodsSkuList,$goodsList);
    }

    /**
     * 导入更新
     * @param $excel_file
     * @param $company_id
     * @return array|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \yii\db\Exception
     */
    public static function importGoods($excel_file, $company_id){
        $spreadsheet = IOFactory::load($excel_file);
        $sheet = $spreadsheet->getSheet(0);
        $totalLine = $sheet->getHighestRow();
        $totalColumn = $sheet->getHighestColumn();
        $rowNo = 1;
        $keyMap = [];
        for ($column = 'A';strlen($column)<strlen($totalColumn)|| $column <= $totalColumn; $column++) {
            $val = trim($sheet->getCell($column.$rowNo) -> getValue());
            $valKey = self::getGoodsImportTitleKeyByTitle($val);
            if ($valKey!=null){
                $keyMap[$column] = $valKey;
            }
        }
        if (count($keyMap)==0){
            return;
        }

        $errData = [];
        $dataes = [];

        $goodOwner = null;
        $bigSortId = null;
        $smallSortId = null;
        $goodsId = null;
        $goodsName = "";
        for ($rowNo = 2; $rowNo <= $totalLine; $rowNo++){
            $data = ['rowNo'=>$rowNo];
            for ($column = 'A'; strlen($column)<strlen($totalColumn)||$column <= $totalColumn; $column++) {
                if (array_key_exists($column,$keyMap)){
                    $data[$keyMap[$column]] = ExcelService::getExcelValue($sheet,$column.$rowNo);
                }
            }
            if (ExcelService::checkExcelValueExist($data,'goods_owner_name')){
                $goodOwner = ArrayUtils::getArrayKey($data['goods_owner_name'],GoodsConstantEnum::$ownerArr,null);
                if (StringUtils::isBlank($goodOwner)){
                    self::assembleNotNullExist($errData,$data,'goods_owner_name');
                    continue;
                }
            }
            if (StringUtils::isBlank($goodOwner)){
                self::assembleNotNullMsg($errData,$data,'goods_owner_name');
                continue;
            }

            if (ExcelService::checkExcelValueExist($data,'sort_1_name')){
                $bigSortModel = GoodsSortService::getByGoodsSortName($data['sort_1_name'],0,$company_id);
                if ($bigSortModel!==null){
                    $bigSortId = $bigSortModel['id'];
                }
                else{
                    self::assembleNotNullExist($errData,$data,'sort_1_name');
                    continue;
                }
            }
            if (StringUtils::isBlank($bigSortId)){
                self::assembleNotNullMsg($errData,$data,'sort_1_name');
                continue;
            }

            if (ExcelService::checkExcelValueExist($data,'sort_2_name')){
                $smallSortModel = GoodsSortService::getByGoodsSortName($data['sort_2_name'],$bigSortId,$company_id);
                if ($smallSortModel!==null){
                    $smallSortId = $smallSortModel['id'];
                }
                else{
                    self::assembleNotNullExist($errData,$data,'sort_2_name');
                    continue;
                }
            }
            if (StringUtils::isBlank($smallSortId)){
                self::assembleNotNullMsg($errData,$data,'sort_2_name');
                continue;
            }

            if (ExcelService::checkExcelValueExist($data,'goods_name')){
                $existGoodsModel = GoodsService::getByGoodsName($data['goods_name'],$company_id,true);
                if ($existGoodsModel!==null){
                    $goodsId = $existGoodsModel['id'];
                    $goodsName = $existGoodsModel['goods_name'];
                }
                else{
                    self::assembleNotNullExist($errData,$data,'goods_name');
                    continue;
                }

                $goodsType = ArrayUtils::getArrayKey($data['goods_type'],GoodsConstantEnum::$typeArr,null);
                if (StringUtils::isBlank($goodsType)){
                    self::assembleNotNullExist($errData,$data,'goods_type');
                    continue;
                }
                $existGoodsModel->goods_type = $goodsType;

                $goodsStatus = ArrayUtils::getArrayKey($data['goods_status'],GoodsConstantEnum::$statusArr,null);
                if (StringUtils::isBlank($goodsStatus)){
                    self::assembleNotNullExist($errData,$data,'goods_status');
                    continue;
                }
                $existGoodsModel->goods_status = $goodsStatus;
                $existGoodsModel->display_order = (integer) ArrayUtils::getArrayValue('goods_display_order',$data,null);

                if (!$existGoodsModel->validate()){
                    $data['error']= ExcelService::assembleErrorMessage($existGoodsModel->errors);
                    $errData[]= $data;
                    continue;
                }

                $data['goodsModel'] = $existGoodsModel;
            }
            if (StringUtils::isBlank($goodsId)){
                self::assembleNotNullMsg($errData,$data,'goods_name');
                continue;
            }

            $data['goods_name'] = $goodsName;
            if (ExcelService::checkExcelValueExist($data,'sku_name')){
                $goodsSkuModel = GoodsSkuService::getByGoodsSkuName($data['sku_name'],$goodsId,$company_id,true);
                if (empty($goodsSkuModel)){
                    self::assembleNotNullExist($errData,$data,'sku_name');
                    continue;
                }

                $skuStatus = ArrayUtils::getArrayKey($data['sku_status'],GoodsConstantEnum::$statusArr,null);
                if (StringUtils::isBlank($skuStatus)){
                    self::assembleNotNullExist($errData,$data,'sku_status');
                    continue;
                }
                $goodsSkuModel->sku_status = $skuStatus;

                $goodsSkuModel->sku_unit =   ArrayUtils::getArrayValue('sku_unit',$data,null);
                $goodsSkuModel->sku_describe =  ArrayUtils::getArrayValue('sku_describe',$data,null);
                $goodsSkuModel->display_order =  ArrayUtils::getArrayValue('sku_display_order',$data,null);
                $goodsSkuModel->sku_unit_factor = (float)  ArrayUtils::getArrayValue('sku_unit_factor',$data,null);
                $goodsSkuModel->purchase_price =  Common::setAmount(ArrayUtils::getArrayValue('purchase_price',$data,0));
                $goodsSkuModel->reference_price =  Common::setAmount(ArrayUtils::getArrayValue('reference_price',$data,0));
                $goodsSkuModel->one_level_rate =  Common::setPercent(ArrayUtils::getArrayValue('one_level_rate',$data,0));
                $goodsSkuModel->two_level_rate =  Common::setPercent(ArrayUtils::getArrayValue('two_level_rate',$data,0));
                $goodsSkuModel->share_rate_1 =  Common::setPercent(ArrayUtils::getArrayValue('share_rate_1',$data,0));
                $goodsSkuModel->delivery_rate =  Common::setPercent(ArrayUtils::getArrayValue('delivery_rate',$data,0));
                $goodsSkuModel->production_date = ExcelService::formatCellYearAndMonthAndDayIfExist($data,'production_date',null);
                $goodsSkuModel->expired_date = ExcelService::formatCellYearAndMonthAndDayIfExist($data,'expired_date',null);

                $goodsSkuModel = ExcelService::setValueIfSetWithDefault($goodsSkuModel,$data,'sku_standard',GoodsSku::SKU_STANDARD_TRUE,GoodsSku::$skuStandardArr);

                if (!$goodsSkuModel->validate()){
                    $data['error']= ExcelService::assembleErrorMessage($goodsSkuModel->errors);
                    $errData[]= $data;
                    continue;
                }
                $data['skuModel'] = $goodsSkuModel;
            }
            $dataes[] = $data;
        }
        if (count($errData)>0){
            return $errData;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        $nowData=[];
        try{
            foreach ($dataes as $data){
                $nowData = $data;
                if (key_exists('goodsModel',$data)){
                    BExceptionAssert::assertTrue($data['goodsModel']->save(),BBusinessException::create("第{$data['rowNo']}行：{$data['goods_name']}{$data['sku_name']}保存失败"));
                }
                if (key_exists('skuModel',$data)){
                    BExceptionAssert::assertTrue($data['skuModel']->save(),BBusinessException::create("第{$data['rowNo']}行：{$data['goods_name']}{$data['sku_name']}保存失败"));

                }
            }
            $transaction->commit();
        }
        catch (\Exception $e){
            $nowData['error']=$e->getMessage();
            $errData[]= $nowData;
            $transaction->rollBack();
        }
        return $errData;
    }

    /**
     * 查询商品header名称
     * @param $title
     * @return int|string|null
     */
    public static function getGoodsImportTitleKeyByTitle($title){
        return ExcelService::getTitleKeyByTitle(DownloadConstants::$goodsSkuList,$title);
    }

    /**
     * 组装非空信息
     * @param $errData
     * @param $data
     * @param $key
     */
    public static function assembleNotNullMsg(&$errData,&$data,$key){
        $data['error'] = DownloadConstants::$goodsSkuList[$key]['title'].'不能为空';
        $errData[] = $data;
    }

    /**
     * 组装不存在信息
     * @param $errData
     * @param $data
     * @param $key
     */
    public static function assembleNotNullExist(&$errData,&$data,$key){
        $data['error'] = DownloadConstants::$goodsSkuList[$key]['title'].'不存在';
        $errData[] = $data;
    }

}