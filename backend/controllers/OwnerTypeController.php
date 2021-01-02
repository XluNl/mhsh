<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\services\OwnerTypeService;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use yii;

/**
 * OwnerType controller
 */
class OwnerTypeController extends BaseController {

	public function actionOptions() {
        $ownerType = Yii::$app->request->get("owner_type",null);
        $companyId = BackendCommon::getFCompanyId();
        try{
            BExceptionAssert::assertNotBlank($ownerType,BBusinessException::create("owner_type不能为空"));
            $optionsArr = OwnerTypeService::getOptionsByOwnerType($ownerType,$companyId);
            return BackendCommon::parseOptions($optionsArr);
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            return "";
        }
	}

}
