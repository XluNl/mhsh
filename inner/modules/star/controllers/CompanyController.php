<?php


namespace inner\modules\star\controllers;

use common\utils\ArrayUtils;
use inner\components\StarControllerInner;
use inner\services\CompanyService;
use Yii;
use yii\helpers\Json;

class CompanyController extends StarControllerInner
{

    public function actionSelect()
    {

        $res = [];
        try {
            $allModels = CompanyService::getAllModel();
            $allModels = ArrayUtils::map($allModels,'id','name');
            $allModels = ArrayUtils::mapToArray($allModels,'thirdPlatFormAgentId','name');
            $res['status'] = 1;
            $res['msg'] = '成功';
            $res['info'] = [];
            $res['info']['lists'] = $allModels;
            return Json::encode($res);
        }
        catch (\Exception $e){
            $res['status'] = 0;
            $res['msg'] = '失败';
            Yii::error('CompanyController.select:'.$e->getMessage());
            return Json::encode($res);
        }
    }

}