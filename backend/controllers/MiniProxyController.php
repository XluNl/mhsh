<?php


namespace backend\controllers;


use backend\services\SystemOptionsService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use common\models\SystemOptions;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

class MiniProxyController extends Controller
{

    public $enableCsrfValidation=false;
    public function actionMeiJia(){
        $xGroupToken = Yii::$app->request->post('xGroupToken');
        $xSessionKey = Yii::$app->request->post('xSessionKey');
        BExceptionAssert::assertNotBlank($xGroupToken,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'xGroupToken'));
        BExceptionAssert::assertNotBlank($xSessionKey,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'xSessionKey'));
        try{
            $jsonData = [
                'xGroupToken'=>$xGroupToken,
                'xSessionKey'=>$xSessionKey,
            ];
            list($result,$errorMsg) = SystemOptionsService::setSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_MEIJIA_COOKIE,Json::encode($jsonData));
            BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::SYSTEM_OPTION_MODIFY_ERROR,$errorMsg));
        }
        catch (\Exception $e){
            return BRestfulResponse::error($e);
        }
        return BRestfulResponse::success(true);
    }
}