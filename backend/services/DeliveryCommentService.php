<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use common\models\DeliveryComment;
use yii\data\ActiveDataProvider;

class DeliveryCommentService
{
    /**
     * 操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $validateException
     */
    public static function operate($id,$commander,$company_id,$operatorId,$operatorName,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[DeliveryComment::STATUS_DELETED,DeliveryComment::STATUS_ACCEPT,DeliveryComment::STATUS_DENY]),$validateException);
        $count = DeliveryComment::updateAll(['status'=>$commander,'operator_id'=>$operatorId,'operator_name'=>$operatorName],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 显示/隐藏操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $validateException
     */
    public static function showOperate($id,$commander,$company_id,$operatorId,$operatorName,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,DeliveryComment::$isShowArr),$validateException);
        $count = DeliveryComment::updateAll(['is_show'=>$commander,'operator_id'=>$operatorId,'operator_name'=>$operatorName],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     *
     * @param $dataProvider ActiveDataProvider
     * @return mixed
     */
    public static function renameImages($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        GoodsDisplayDomainService::batchRenameImageUrl($models,'images');
        $dataProvider->setModels($models);
        return $dataProvider;
    }

}