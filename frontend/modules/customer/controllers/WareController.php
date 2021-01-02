<?php


namespace frontend\modules\customer\controllers;
use backend\models\BackendCommon;
use backend\models\searches\DeliveryManagementSearch;
use backend\models\searches\GoodsSearch;
use backend\services\DownloadService;
use backend\services\RouteService;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\params\RedirectParams;
use common\utils\DateTimeUtils;
use frontend\components\FController;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 仓库接口
 * Class WareController
 * @package frontend\modules\customer\controllers
 */
class WareController extends FController {

    /**
     * 配送商品接口
     * @return false|string
     */
    public function actionSales_goods_list(){
        $this->enableCsrfValidation = false;
        $searchModel = new DeliveryManagementSearch();
        BackendCommon::addCompanyIdToParams('DeliveryManagementSearch');
        $params = Yii::$app->request->bodyParams;
        $params['company_id'] = 1;
        Yii::$app->request->bodyParams = ArrayHelper::merge(Yii::$app->request->bodyParams,['DeliveryManagementSearch'=>$params]);
        $dataProvider = $searchModel->search(Yii::$app->request->bodyParams);
        $data = [];
        foreach ($dataProvider->query->all() as $dm){
            $da = ['product_id'=>$dm['goods_id'],'title'=>$dm['goods_name'], 'sales_number'=>$dm['sold_amount'], 'goods_owner' => $dm['goods_owner'], 'wait_send_number'=>$dm['un_delivery_amount'], 'make_name'=>'', 'expect_arrive_time'=>$dm['expect_arrive_time'], 'status'=>$dm['order_status']];
            $data[] = $da;
        }
        $datas = [];
        $datas['data'] = $data;
        return json_encode($datas);
    }

    /**
     * 所有团点
     * @return false|string
     */
    public function actionMember_list(){
        $this->enableCsrfValidation = false;
        $deliveryModels = RouteService::getDeliveryByRouteId(-1, 1);
        $data = [];
        foreach ($deliveryModels as $dm){
            $da = ['uid'=>$dm['id'], 'username'=>$dm['realname'], 'lng'=>$dm['lng'], 'lat'=>$dm['lat'], 'company'=>'满好'];
            $data[] = $da;
        }
        $datas = [];
        $datas['data'] = $data;
        return json_encode($datas);
    }

    /**
     * 商品列表
     * @return false|string
     */
    public function actionGoods_list(){
        $this->enableCsrfValidation = false;
        $searchModel = new GoodsSearch();
        $params = Yii::$app->request->bodyParams;
        $params['company_id'] = 1;
        Yii::$app->request->bodyParams = ArrayHelper::merge(Yii::$app->request->bodyParams,['GoodsSearch'=>$params]);
        $similarity = Yii::$app->request->post('similarity', '');
        $page = Yii::$app->request->post('page', 0);
        $page_size = Yii::$app->request->post('page_size', 10);
        $dataProvider = $searchModel->search(Yii::$app->request->bodyParams);
        $data = [];
        foreach ($dataProvider->query->all() as $dm){
            $da = ['product_id' => $dm['id'], 'title' => $dm['goods_name'], 'purchase_model'=>$dm['goods_describe'], 'similarity'=>similar_text($dm['goods_name'], $similarity), 'company'=>'满好', 'attachment'=>'https://image.manhaoshenghuo.cn'.$dm['goods_img']];
            $data[] = $da;
        }

        $last_names = array_column($data,'similarity');
        array_multisort($last_names,SORT_DESC,$data);//根据相似度倒序

        $data = array_slice($data, ($page-1) * $page_size, $page_size);
        $datas = [];
        $datas['data'] = $data;
        return json_encode($datas);
    }

    /**
     * 配送团长接收确认单
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionDeliveryGoodsList()
    {
        $this->enableCsrfValidation = false;
        $sortingDate = Yii::$app->request->get('date');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        $orderOwner = Yii::$app->request->get('order_owner');
        BExceptionAssert::assertNotBlank($sortingDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'date'));
//        $company_id = BackendCommon::getFCompanyId();
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        DownloadService::downloadDeliveryGoods($sortingDate,$orderOwner,$orderTimeStart,$orderTimeEnd,1);
        return;
    }

    /**
     * 团长订单明细导出
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * zwb
     */
    public function actionOrderList(){
        $sortingDate = Yii::$app->request->get('date');
        $orderOwner = Yii::$app->request->get('order_owner');
        BExceptionAssert::assertNotBlank($sortingDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'date'));

        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",Yii::$app->request->referrer));
        DownloadService::downloadOrderList($sortingDate,$orderOwner,1);
        return;
    }


}