<?php
namespace alliance\modules\alliance\controllers;

use alliance\components\FController;
use alliance\services\RegionService;
use alliance\utils\RestfulResponse;
use Yii;

class RegionController extends FController {

    public function actionList(){
        $parentId = Yii::$app->request->get('parent_id',0);
        $data = RegionService::getRegionByParentId($parentId);
        if (330100==$parentId){
            foreach ($data as $k=>$v){
                if ($v['id']==330101){
                    unset($data[$k]);
                }
            }
            $data = array_values($data);
        }
        return RestfulResponse::success($data);
    }

}