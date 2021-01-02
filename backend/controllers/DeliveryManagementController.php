<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\forms\DeliveryGoodsListSearchForms;
use backend\models\forms\OrderDeliverySearchForms;
use backend\models\searches\DeliveryManagementSearch;
use backend\services\DeliveryManagementService;
use backend\services\DeliveryService;
use backend\services\RegionService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use common\utils\StringUtils;
use kartik\grid\EditableColumnAction;
use yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * DeliveryManagement controller
 */
class DeliveryManagementController extends BaseController {

    public function actionIndex(){
        $searchModel = new DeliveryManagementSearch();
        BackendCommon::addCompanyIdToParams('DeliveryManagementSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModifyExpectArriveTime(){
        if (Yii::$app->request->post('hasEditable')) {
            $company_id = BackendCommon::getFCompanyId();
            $scheduleId = Yii::$app->request->post('editableKey');
            $editableAttribute =  Yii::$app->request->post('editableAttribute');
            $out = ['output' => ''];
            if (StringUtils::isBlank($scheduleId)){
                $out['message'] = '排期ID不能为空';
            }
            else if ($editableAttribute=='expect_arrive_time'){
                $expectArriveTime = Yii::$app->request->post($editableAttribute);
                if (StringUtils::isBlank($expectArriveTime)){
                    $out['message'] = '不能为空值';
                }
                else{
                    $out['output'] = $expectArriveTime;
                    list($result,$errorMsg) = DeliveryManagementService::modifyExpectArriveTime($scheduleId,$expectArriveTime,$company_id);
                    if (!$result){
                        $out['message'] = $errorMsg;
                    }
                }
            }
            else{
                $out['message'] = '不支持的修改值';
            }
            return Json::encode($out);
        }
        return "";
    }


    public function actionDeliveryOut(){
        $companyId = BackendCommon::getFCompanyId();
        $scheduleIds = Yii::$app->request->get("scheduleIds");
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        BExceptionAssert::assertNotBlank($scheduleIds,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'scheduleIds'));
        $scheduleIds = explode(",", $scheduleIds);
        BExceptionAssert::assertNotEmpty($scheduleIds,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'scheduleIds'));
        $userId = BackendCommon::getUserId();
        $username = BackendCommon::getUserName();
        list($result,$errorMsg) = DeliveryManagementService::deliveryOut($scheduleIds,$orderTimeStart,$orderTimeEnd,$companyId,$userId,$username);
        if (!$result){
            return BRestfulResponse::errorBusyError(BBusinessException::create($errorMsg));
        }
        return BRestfulResponse::success($errorMsg);
    }


    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'modify-expect-arrive-time-1' => [                                       // identifier for your editable action
                'class' => EditableColumnAction::className(),     // action class name
                //'modelClass' => Book::className(),                // the update model class
                'outputValue' => function ($model, $attribute, $key, $index) {
                    $fmt = \Yii::$app->formatter;
                    $value = $model->$attribute;                 // your attribute value
                    if ($attribute === 'buy_amount') {           // selective validation by attribute
                        return $fmt->asDecimal($value, 2);       // return formatted value if desired
                    } elseif ($attribute === 'publish_date') {   // selective validation by attribute
                        return $fmt->asDate($value, 'php:Y-m-d');// return formatted value if desired
                    }
                    return '';                                   // empty is same as $value
                },
                'outputMessage' => function($model, $attribute, $key, $index) {
                    return '';                                  // any custom error after model save
                },
                // 'showModelErrors' => true,                     // show model errors after save
                // 'errorOptions' => ['header' => '']             // error summary HTML options
                // 'postOnly' => true,
                // 'ajaxOnly' => true,
                // 'findModel' => function($id, $action) {},
                // 'checkAccess' => function($action, $model) {}
            ]
        ]);
    }



    public function actionOrderDeliveryIndex(){
        $searchModel = new OrderDeliverySearchForms();
        BackendCommon::addCompanyIdToParams('OrderDeliverySearchForms');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        RegionService::batchSetProvinceAndCityAndCountyForOrderProvider($dataProvider);
        $companyId = BackendCommon::getFCompanyId();
        $searchModel->deliveryOptions = DeliveryService::generateAllDeliveryOptions($companyId);
        return $this->render('order-delivery-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionDeliveryGoodsListIndex(){
        $searchModel = new DeliveryGoodsListSearchForms();
        BackendCommon::addCompanyIdToParams('DeliveryGoodsListSearchForms');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $companyId = BackendCommon::getFCompanyId();
        $searchModel->deliveryOptions = DeliveryService::generateAllDeliveryOptions($companyId);
        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
        return $this->render('delivery-goods-list-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
