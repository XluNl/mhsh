<?php

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\GroupRoomOrderSearch;
use backend\services\CustomerService;
use backend\services\GoodsService;
use backend\services\GroupByService;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\GoodsConstantEnum;
use common\models\GroupRoom;
use common\utils\StringUtils;
use Yii;

class GroupRoomOrderController extends BaseController
{
    public function actionIndex()
    {	
        $searchModel = new GroupRoomOrderSearch();
        BackendCommon::addCompanyIdToParams('GroupRoomOrderSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $companyId = BackendCommon::getFCompanyId();
        if (StringUtils::isNotBlank($searchModel->owner_type)){
            $searchModel->goodsOptions = GoodsService::getListByGoodsOwnerOptionsNoErr($companyId,$searchModel->owner_type);
        }
    	return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    public function actionDetail(){
        $order_no = Yii::$app->request->get("order_no");
        BExceptionAssert::assertNotBlank($order_no,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"order_no"));
        $company_id = BackendCommon::getFCompanyId();
        $roomInfo = GroupByService::getGroupRoomByOrderNo($order_no);
        $roomInfo = GroupByService::getActiveInfo($roomInfo['id']);
        $roomInfo['number']  =  GroupByService::getInGroupRoomNmber($roomInfo['id']);
        $roomInfo['littleTime'] = GroupByService::calActiveLittleTime($roomInfo['created_at'],$roomInfo['activeInfo']['continued'],$roomInfo['activeInfo']['schedule']['offline_time']);

        $teamInfo = CustomerService::getModelWithUser($roomInfo['team_id'],$company_id);

        $goodsInfo = GroupByService::getGoodInfoByGroupId($roomInfo['id']);
        
        list($orders,$orderAmount) = GroupByService::getGroupRoomUsersByGroupId($roomInfo['id']);

        return $this->render('detail',['roomInfo'=>$roomInfo,'goodsInfo'=>$goodsInfo,'teamInfo'=>$teamInfo,'orders'=>$orders]);
    }

}
