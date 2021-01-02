<?php

namespace frontend\modules\customer\controllers;
use business\services\DeliveryCommentVOService;
use frontend\components\FController;
use frontend\services\DeliveryCommentService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class DeliveryCommentController extends FController {

    public function actionList() {
        $goodsId = Yii::$app->request->get("goods_id");
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 10);
        ExceptionAssert::assertNotBlank($goodsId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods_id'));
        $comments = DeliveryCommentService::getShowListByGoodsId($goodsId,$pageNo,$pageSize);
        $goodsList = DeliveryCommentVOService::batchDefineStatusVO($comments);
        return RestfulResponse::success($goodsList);
    }

    public function actionDetail() {
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotBlank($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $comment = DeliveryCommentService::getShowModelById($id);
        $comment = DeliveryCommentVOService::defineStatusVO($comment);
        return RestfulResponse::success($comment);
    }


}