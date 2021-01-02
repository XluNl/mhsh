<?php

namespace business\modules\delivery\controllers;
use business\models\BusinessCommon;
use business\services\OrderCustomerServiceService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\GoodsConstantEnum;
use yii;
use yii\web\Controller;

class CustomerServiceController extends Controller
{


    /**
     * 售后列表
     * @return false|string
     */
    public function actionList(){
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotBlank($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no'));
        $userId = BusinessCommon::requiredUserId();
        $list = OrderCustomerServiceService::getListByOrder($orderNo,$userId);
        return RestfulResponse::success($list);
    }

    /**
     * 售后操作
     * @return string
     * @throws \business\utils\exceptions\BusinessException
     * @throws yii\db\Exception
     */
    public function actionOperation(){
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        $auditRemark = Yii::$app->request->get("audit_remark");
        ExceptionAssert::assertNotBlank($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        ExceptionAssert::assertNotBlank($commander,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'commander'));
        $userId = BusinessCommon::requiredUserId();
        $userName = BusinessCommon::requiredUserName();
        OrderCustomerServiceService::operate($id,$commander,$userId,$userName,$auditRemark);
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
        $ownerType = Yii::$app->request->get("owner_type", GoodsConstantEnum::OWNER_SELF);
        $status = Yii::$app->request->get("status");
        $delivery = BusinessCommon::requiredDelivery();
        $list = OrderCustomerServiceService::getListPageFilter($ownerType,$delivery['id'],$status,$keyword,$pageNo,$pageSize);
        return RestfulResponse::success($list);
    }

}
