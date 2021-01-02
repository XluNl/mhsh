<?php


namespace backend\controllers;


use backend\services\SystemOptionsService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use Yii;
use yii\data\ArrayDataProvider;

class SystemOptionsController extends BaseController
{

    public function actionSystemIndex(){
        $systemOptionsModels = SystemOptionsService::getSystemOptionsList();
        $dataProvider = new ArrayDataProvider([
            'allModels' => $systemOptionsModels
        ]);
        return $this->render('system-index', [
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionSystemModify(){
        $optionField = Yii::$app->request->post('option_field');
        $optionValue = Yii::$app->request->post('option_value');
        BExceptionAssert::assertNotBlank($optionField,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'option_field'));
        BExceptionAssert::assertNotBlank($optionValue,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'option_value'));
        $optionValue = trim($optionValue);
        try{
            list($result,$errorMsg) = SystemOptionsService::setSystemOptionValue($optionField,$optionValue);
            BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::SYSTEM_OPTION_MODIFY_ERROR,$errorMsg));
        }
        catch (\Exception $e){
            return BRestfulResponse::error($e);
        }
        return BRestfulResponse::success("修改成功");
    }


}