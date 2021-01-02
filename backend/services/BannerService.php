<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\models\Banner;
use common\models\Common;
use common\models\GoodsSchedule;
use common\models\CommonStatus;
use common\utils\DateTimeUtils;
use yii\helpers\Json;

class BannerService  extends \common\services\BannerService
{


    /**
     * 非空
     * @param $id
     * @param $companyId
     * @param $validateException RedirectParams
     * @param bool $model
     * @return array|bool|\common\models\Banner|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id, $companyId, $validateException, $model = false){
        $model = self::getModel($id,$companyId,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        $model = self::restoreLinkInfo($model);
        return $model;
    }


    /**
     * 状态操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operateStatus($id, $commander, $company_id, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[CommonStatus::STATUS_ACTIVE,CommonStatus::STATUS_DISABLED,CommonStatus::STATUS_DELETED]),$validateException);
        $count = Banner::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

}