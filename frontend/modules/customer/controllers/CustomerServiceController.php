<?php

namespace frontend\modules\customer\controllers;
use common\models\GoodsConstantEnum;
use common\models\OrderCustomerService;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\services\OrderCustomerServiceService;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use yii;
use yii\web\Controller;

class CustomerServiceController extends Controller
{
    /**
     * 申请售后
     * @return false|string
     * @throws BusinessException
     * @throws yii\db\Exception
     */
    public function actionApply()
    {
        $order_no = Yii::$app->request->get("order_no");
        $order_goods_ids = Yii::$app->request->get("order_goods_ids");
        $customer_service_type = Yii::$app->request->get("type");
        $remark = Yii::$app->request->get("remark");
        $images = Yii::$app->request->get("images");
        ExceptionAssert::assertNotNull($order_no,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no'));
        ExceptionAssert::assertNotNull($order_goods_ids,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_goods_ids'));
        ExceptionAssert::assertNotNull($customer_service_type,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'type'));
        $orderGoodsIdsArr = explode(',',$order_goods_ids);
        OrderCustomerServiceService::apply($order_no,$orderGoodsIdsArr,$customer_service_type,$remark,$images);
        return RestfulResponse::success(true);
    }

    public function actionClaimApply()
    {
        $order_no = Yii::$app->request->get("order_no");
        $order_goods_id = Yii::$app->request->get("order_goods_id");
        $claimAmount = Yii::$app->request->get("claim_amount");
        $remark = Yii::$app->request->get("remark");
        $images = Yii::$app->request->get("images");
        ExceptionAssert::assertNotNull($order_no,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no'));
        ExceptionAssert::assertNotNull($order_goods_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_goods_id'));
        ExceptionAssert::assertTrue($claimAmount>=0.01,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'最小金额为0.01元'));
        OrderCustomerServiceService::apply($order_no,[$order_goods_id],OrderCustomerService::TYPE_REFUND_CLAIM,$remark,$images,[$claimAmount]);
        return RestfulResponse::success(true);
    }

    /**
     * 售后列表
     * @return false|string
     */
    public function actionList(){
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotBlank($orderNo, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no'));
        $customerId = FrontendCommon::requiredCustomerId();
        $list = OrderCustomerServiceService::getListByOrder($orderNo,$customerId);
        return RestfulResponse::success($list);
    }

    /**
     * 取消售后
     * @return false|string
     * @throws BusinessException
     */
    public function actionCancel(){
        $customerServiceId = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($customerServiceId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $customerModel = FrontendCommon::requiredActiveCustomer();
        OrderCustomerServiceService::cancel($customerModel,$customerServiceId);
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
        $ownerTypes = Yii::$app->request->get("owner_type", null);
        if (StringUtils::isNotBlank($ownerTypes)){
            $ownerTypes = ExceptionAssert::assertNotBlankAndNotEmpty($ownerTypes,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no'));
        }
        $status = Yii::$app->request->get("status");
        $customerId = FrontendCommon::requiredCustomerId();
        $list = OrderCustomerServiceService::getListPageFilter($ownerTypes,$customerId,$status,$keyword,$pageNo,$pageSize);
        return RestfulResponse::success($list);
    }
}
