<?php


namespace business\modules\delivery\controllers;


use business\components\FController;
use business\models\BusinessCommon;
use business\services\GoodsSortService;
use business\utils\RestfulResponse;
use common\models\GoodsConstantEnum;
use Yii;

class GoodsSortController extends FController
{

    public function actionList() {
        $bigSortId = Yii::$app->request->get("big_sort", 0);
        $ownerType = Yii::$app->request->get("owner_type", GoodsConstantEnum::OWNER_SELF);
        $companyId = BusinessCommon::getFCompanyId();
        $sortList = GoodsSortService::getGoodsSortList($companyId,$ownerType,$bigSortId);
        return RestfulResponse::success($sortList);
    }

}