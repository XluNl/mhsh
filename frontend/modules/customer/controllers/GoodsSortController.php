<?php


namespace frontend\modules\customer\controllers;


use common\models\GoodsConstantEnum;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\GoodsSortService;
use frontend\utils\RestfulResponse;
use Yii;

class GoodsSortController extends FController
{

    public function actionList(){
        $ownerType = Yii::$app->request->get("ownerType",GoodsConstantEnum::OWNER_SELF);
        $companyId = FrontendCommon::requiredFCompanyId();
        $sortList = GoodsSortService::getGoodsSortList($companyId,$ownerType,0);
        return RestfulResponse::success($sortList);
    }
}