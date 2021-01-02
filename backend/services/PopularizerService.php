<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use common\models\CommonStatus;
use common\models\Popularizer;

class PopularizerService extends \common\services\PopularizerService
{
    public static function operate($id,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,CommonStatus::$StatusArr),$validateException);
        $count = Popularizer::updateAll(['status'=>$commander],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 获取并校验
     * @param $id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|Popularizer|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id,$company_id,$validateException,$model = false){
        $model = self::getActiveModel($id,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }
}