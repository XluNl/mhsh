<?php


namespace alliance\modules\alliance\controllers;


use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\GoodsSortService;
use alliance\utils\RestfulResponse;
use common\models\GoodsConstantEnum;
use Yii;

class GoodsSortController extends FController
{

    public function actionList() {
        $bigSortId = Yii::$app->request->get("big_sort", 0);
        $companyId = AllianceCommon::getFCompanyId();
        $sortList = GoodsSortService::getGoodsSortList($companyId,GoodsConstantEnum::OWNER_HA,$bigSortId);
        return RestfulResponse::success($sortList);
    }

}