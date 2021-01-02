<?php


namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\DeliveryComment;

class DeliveryCommentService
{
    /**
     * 创建团长说申请
     * @param $userId
     * @param $deliveryId
     * @param $skuId
     * @param $images
     * @param $comment
     * @param $companyId
     */
    public static function create($userId,$deliveryId,$skuId,$images,$comment,$companyId){
        $goodsSkuList = GoodsSkuService::getSkuInfoById([$skuId],null,$companyId);
        ExceptionAssert::assertNotEmpty($goodsSkuList,StatusCode::createExpWithParams(StatusCode::DELIVERY_COMMENT_OPERATION_ERROR,'商品不存在'));
        $goodsId = $goodsSkuList[0]['id'];
        $model = new DeliveryComment();
        $model->user_id = $userId;
        $model->delivery_id = $deliveryId;
        $model->company_id = $companyId;
        $model->goods_id = $goodsId;
        $model->sku_id = $skuId;
        $model->status =DeliveryComment::STATUS_APPLY;
        $model->images = $images;
        $model->content = $comment;
        $model->is_show = DeliveryComment::IS_SHOW_FALSE;
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::DELIVERY_COMMENT_OPERATION_ERROR,'保存失败'));
    }

    /**
     * 获取list
     * @param $userId
     * @param $pageNo
     * @param $pageSize
     * @return array|DeliveryComment[]|\yii\db\ActiveRecord[]
     */
    public static function getList($userId,$pageNo,$pageSize){
        $list = DeliveryComment::find()->where([
            'user_id'=>$userId,
            'status'=>[DeliveryComment::STATUS_APPLY,DeliveryComment::STATUS_ACCEPT,DeliveryComment::STATUS_DENY]
            ])->offset(($pageNo-1)*$pageSize)->limit($pageSize)->with(['goods','goodsSku','delivery'])->orderBy('id desc')->asArray()->all();
        return $list;
    }

}