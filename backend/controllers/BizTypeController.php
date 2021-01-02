<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\services\BizTypeService;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use yii;

/**
 * BizType controller
 */
class BizTypeController extends BaseController {

	public function actionOptions() {
        $bizType = Yii::$app->request->get("biz_type",null);
        try{
            BExceptionAssert::assertNotBlank($bizType,BBusinessException::create("biz_type不能为空"));
            $optionsArr = BizTypeService::getOptionsByBizType($bizType);
            return BackendCommon::parseOptions($optionsArr);
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            return "";
        }
	}

}
