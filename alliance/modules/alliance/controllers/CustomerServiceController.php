<?php

namespace alliance\modules\alliance\controllers;
use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\OrderCustomerServiceService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use common\models\GoodsConstantEnum;
use yii;

class CustomerServiceController extends FController
{


    /**
     * 订单售后列表
     * @return false|string
     */
    public function actionList(){
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotBlank($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no'));
        $userId = AllianceCommon::requiredUserId();
        $list = OrderCustomerServiceService::getListByOrder($orderNo,$userId);
        return RestfulResponse::success($list);
    }

    /**
     * 售后操作
     * @return string
     * @throws \alliance\utils\exceptions\BusinessException
     * @throws yii\db\Exception
     */
    public function actionOperation(){
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotBlank($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        ExceptionAssert::assertNotBlank($commander,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'commander'));
        $userId = AllianceCommon::requiredUserId();
        $userName = AllianceCommon::requiredUserName();
        OrderCustomerServiceService::operate($id,$commander,$userId,$userName);
        return RestfulResponse::success(true);
    }

    /**
     * 售后列表
     * @return false|string
     */
    public function actionPageList(){
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $keyword = Yii::$app->request->get("search_keyword",null);
        $status = Yii::$app->request->get("status");
        $alliance = AllianceCommon::requiredAlliance();
        $list = OrderCustomerServiceService::getListPageFilter(GoodsConstantEnum::OWNER_HA,$alliance['id'],$status,$keyword,$pageNo,$pageSize);
        return RestfulResponse::success($list);
    }

}
