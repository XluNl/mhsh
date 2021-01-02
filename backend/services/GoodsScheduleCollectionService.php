<?php


namespace backend\services;


use backend\models\constants\DownloadConstants;
use backend\models\forms\GoodsScheduleImportForm;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\models\Common;
use common\models\CommonStatus;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsScheduleCollection;
use common\models\GoodsSku;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\db\Query;

class GoodsScheduleCollectionService extends \common\services\GoodsScheduleCollectionService
{
    /**
     * 必须
     * @param $id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|\common\models\GoodsScheduleCollection|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id,$company_id,$validateException,$model = false){
        $model = self::getActiveModel($id,$company_id,null,null,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException RedirectParams
     */
    public static function operate($id,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,[CommonStatus::STATUS_DISABLED]),$validateException->updateMessage("未知的操作类型"));
        $scheduleModels = GoodsScheduleService::getActiveGoodsScheduleByCollectionId($id,$company_id,null);
        BExceptionAssert::assertEmpty($scheduleModels,$validateException->updateMessage("子排序必须为空才能删除"));
        $count = GoodsScheduleCollection::updateAll(['status'=>$commander],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException->updateMessage("删除操作失败"));
    }

    /**
     * 排期批量上下线
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function scheduleOperate($id,$commander,$company_id,$ids,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[GoodsConstantEnum::STATUS_UP,GoodsConstantEnum::STATUS_DOWN,GoodsConstantEnum::STATUS_DELETED]),$validateException);
        $condition = ['collection_id'=>$id,'company_id'=>$company_id];
        if(!empty($ids)){
            $condition['id'] = $ids;
        }
        $count = GoodsSchedule::updateAll(['schedule_status'=>$commander],$condition);
        BExceptionAssert::assertTrue($count!==false,$validateException);
    }


    /**
     * 根据title查找排期名称
     * @param $title
     * @return int|string|null
     */
    public static function getGoodsScheduleImportTitleKeyByTitle($title){
        return ExcelService::getTitleKeyByTitle(DownloadConstants::$scheduleList,$title);
    }


    /**
     * 导入排期
     * @param $goodsScheduleCollectionModel GoodsScheduleCollection
     * @param $model  GoodsScheduleImportForm
     * @param $excel_file
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @return array|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \yii\db\Exception
     */
    public static function import($goodsScheduleCollectionModel,$model,$excel_file,$company_id,$operatorId,$operatorName){
        $spreadsheet = IOFactory::load($excel_file);
        $sheet = $spreadsheet->getSheet(0);
        $total_line = $sheet->getHighestRow();
        $total_column = $sheet->getHighestColumn();
        $rowNo = 1;
        $keyMap = [];
        for ($column = 'A';strlen($column)<strlen($total_column)|| $column <= $total_column; $column++) {
            $val = trim($sheet->getCell($column.$rowNo) -> getValue());
            $valKey = self::getGoodsScheduleImportTitleKeyByTitle($val);
            if ($valKey!=null){
                $keyMap[$column] = $valKey;
            }
        }
        if (count($keyMap)==0){
            return;
        }

        $errData = [];
        $dataes = [];
        for ($rowNo = 2; $rowNo <= $total_line; $rowNo++){
            $data = ['rowNo'=>$rowNo];
            for ($column = 'A'; strlen($column)<strlen($total_column)||$column <= $total_column; $column++) {
                if (array_key_exists($column,$keyMap)){
                    $data[$keyMap[$column]] = ExcelService::getExcelValue($sheet,$column.$rowNo);
                }
            }
            if (!ExcelService::checkExcelValueExist($data,'goods_name')){
                $data['error']="商品名称必填";
                $errData[]= $data;
                continue;
            }
            if (!ExcelService::checkExcelValueExist($data,'sku_name')){
                $data['error']="属性名称必填";
                $errData[]= $data;
                continue;
            }
            if (!ExcelService::checkExcelValueExist($data,'schedule_name')){
                $data['error']="排期名称必填";
                $errData[]= $data;
                continue;
            }
            $scheduleModel = new GoodsSchedule();
            $scheduleModel->collection_id = $model->collection_id;
            $scheduleModel->company_id = $company_id;
            $scheduleModel->operation_name = $operatorName;
            $scheduleModel->operation_id = $operatorId;
            $goodsModel = GoodsService::getByGoodsName($data['goods_name'],$company_id);
            if ($goodsModel===null){
                $data['error']="商品不存在";
                $errData[]= $data;
                continue;
            }
            $scheduleModel->goods_id = $goodsModel['id'];
            $goodsSkuModel = GoodsSkuService::getByGoodsSkuName($data['sku_name'],$goodsModel['id'],$company_id);
            if ($goodsSkuModel===null){
                $data['error']="属性不存在";
                $errData[]= $data;
                continue;
            }
            $scheduleModel->sku_id = $goodsSkuModel['id'];
            $scheduleModel->schedule_name = $data['schedule_name'];
            $scheduleModel->online_time = $data['online_time'];
            $scheduleModel->offline_time = $data['offline_time'];
            $scheduleModel->display_start = $data['display_start'];
            $scheduleModel->display_end = $data['display_end'];
            $scheduleModel->expect_arrive_time = DateTimeUtils::formatYearAndMonthAndDay($data['expect_arrive_time']);
            $scheduleModel->validity_start = $data['validity_start'];
            $scheduleModel->validity_end = $data['validity_end'];



            $scheduleModel->display_order = (integer) ArrayUtils::getArrayValue('display_order',$data,null);
            $scheduleModel->price = (integer)Common::setAmount($data['schedule_price']);
            $scheduleModel->schedule_stock = (integer)$data['schedule_stock'];
            $scheduleModel->schedule_limit_quantity = (integer)$data['schedule_limit_quantity'];

            $scheduleModel->owner_type = $goodsScheduleCollectionModel['owner_type'];
            $scheduleModel->owner_id = $goodsScheduleCollectionModel['owner_id'];

            //绑定仓库商品关系和比例
            $storageSkuMapping = StorageSkuMappingService::getModel($scheduleModel->sku_id,$company_id);
            if (!empty($storageSkuMapping)){
                $scheduleModel->storage_sku_id = $storageSkuMapping['storage_sku_id'];
                $scheduleModel->storage_sku_num = $storageSkuMapping['storage_sku_num'];
            }


            $scheduleModel = ExcelService::setValueIfSetWithDefault($scheduleModel,$data,'schedule_status',GoodsConstantEnum::STATUS_ACTIVE,GoodsConstantEnum::$statusArr);
            $scheduleModel = ExcelService::setValueIfSetWithDefault($scheduleModel,$data,'schedule_display_channel',GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_NORMAL,GoodsConstantEnum::$scheduleDisplayChannelArr);
            $scheduleModel = ExcelService::setValueIfSetWithDefault($scheduleModel,$data,'goods_owner',GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::$ownerArr);




            if (!$scheduleModel->validate()){
                $data['error']= ExcelService::assembleErrorMessage($scheduleModel->errors);
                $errData[]= $data;
                continue;
            }
            $data['model'] = $scheduleModel;
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
                BExceptionAssert::assertTrue($data['model']->save(),BBusinessException::create("第{$data['rowNo']}行：{$data['goods_name']}{$data['sku_name']}保存失败"));
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
     *
     * @param $collectionId
     * @param $companyId
     * @return string
     */
    public static function getScheduleGoodsText($collectionId,$companyId){
        $scheduleTable = GoodsSchedule::tableName();
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $collectionGoods = (new Query())->from($scheduleTable)
            ->leftJoin($goodsTable,"{$goodsTable}.id={$scheduleTable}.goods_id")
            ->leftJoin($skuTable,"{$skuTable}.id={$scheduleTable}.sku_id")
            ->where([
                "{$scheduleTable}.collection_id"=>$collectionId,
                "{$scheduleTable}.company_id"=>$companyId,
                "{$scheduleTable}.schedule_status"=>GoodsConstantEnum::$activeStatusArr,
                "{$goodsTable}.goods_status"=>GoodsConstantEnum::$activeStatusArr,
                "{$skuTable}.sku_status"=>GoodsConstantEnum::$activeStatusArr,
            ])
            ->orderBy("{$goodsTable}.sort_1 asc,{$scheduleTable}.display_order desc")
            ->all();
        GoodsSortService::completeSortName($collectionGoods);
        return $collectionGoods;
    }


}