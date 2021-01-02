<?php

namespace business\modules\delivery\controllers;

use business\components\FController;
use business\models\BusinessCommon;
use business\services\DeliveryCommentService;
use business\services\DeliveryCommentVOService;
use business\services\DeliveryService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use Yii;

class DeliveryCommentController extends FController {

    public function actionCreate(){
        $images = Yii::$app->request->get("images");
        $comment = Yii::$app->request->get("comment");
        $sku_id = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($sku_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        ExceptionAssert::assertNotNull($comment,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'comment'));
        ExceptionAssert::assertNotNull($images,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'images'));
        $userId= BusinessCommon::requiredUserId();
        $deliveryId = DeliveryService::getSelectedDeliveryId($userId);
        $companyId = BusinessCommon::getFCompanyId();
        DeliveryCommentService::create($userId,$deliveryId,$sku_id,$images,$comment,$companyId);
        return RestfulResponse::success(true);
    }

    public function actionList(){
        $pageNo = Yii::$app->request->get("page_no",1);
        $pageSize = Yii::$app->request->get("page_size",20);
        $userId= BusinessCommon::requiredUserId();
        $data = DeliveryCommentService::getList($userId,$pageNo,$pageSize);
        $data = DeliveryCommentVOService::batchDefineStatusVO($data);
        return RestfulResponse::success($data);
    }


}
