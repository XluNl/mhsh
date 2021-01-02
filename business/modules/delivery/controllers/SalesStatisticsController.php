<?php
namespace business\modules\delivery\controllers;

use business\components\FController;
use business\services\RegionService;
use business\utils\RestfulResponse;
use business\models\BusinessCommon;
use Yii;

class SalesStatisticsController extends FController {

	/**
	 * [actionSell 佣金统计]
	 * @return [type] [description]
	 */
    public function actionSell(){
        $delivery_id = Yii::$app->request->get("delivery_id");
        $userId = BusinessCommon::requiredUserId();
        return RestfulResponse::success($data);
    }

    /**
     * [actionSales 销售统计]
     * @return [type] [description]
     */
    public function actionSales(){
        $delivery_id = Yii::$app->request->get("delivery_id");
        return RestfulResponse::success($data);
    }

}