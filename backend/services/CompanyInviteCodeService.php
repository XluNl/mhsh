<?php


namespace backend\services;



use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\models\Common;
use common\models\CompanyInviteCode;
use common\utils\PathUtils;
use common\utils\UUIDUtils;
use EasyWeChat\Kernel\Http\StreamResponse;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;
use yii\helpers\FileHelper;

class CompanyInviteCodeService extends \common\services\CompanyInviteCodeService
{

    public static function getShowModel($companyId){
        $model = parent::getModelById($companyId);
        if (empty($model)){
            $model = new CompanyInviteCode();
        }
        return $model;
    }


    /**
     * 生成团长邀请码
     * @param $companyId
     * @param $validateException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \yii\base\Exception
     */
    public static function refreshBusinessCode($companyId,$validateException){
        $scene = "company_id={$companyId}";
        $response = Yii::$app->businessWechat->miniProgram->app_code->getUnlimit($scene,['width'=>430,'page'=>'pages/index/login/authorization'] );
        if (!$response instanceof StreamResponse){
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($response['errmsg']));
        }
        $filename = UUIDUtils::uuidWithoutSeparator().".png";
        $imagePath = PathUtils::joins(Yii::getAlias("@public"),"uploads","qrcode");
        $imageUrl = PathUtils::joins("/uploads","qrcode",$filename);
        FileHelper::createDirectory($imagePath,777,true);
        $response->saveAs($imagePath,$filename);
        $model = parent::getModelById($companyId);
        if (empty($model)){
            $model = new CompanyInviteCode();
        }
        $model->company_id = $companyId;
        $model->business_invite_image = $imageUrl;
        BExceptionAssert::assertTrue($model->save(),$validateException->updateMessage(Common::getModelErrors($model)));
    }


    /**
     * 生成联盟邀请码
     * @param $companyId
     * @param $validateException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \yii\base\Exception
     */
    public static function refreshAllianceCode($companyId,$validateException){
        $scene = "company_id={$companyId}";
        $response = Yii::$app->allianceWechat->miniProgram->app_code->getUnlimit($scene,['width'=>430,'page'=>'pages/index/login/authorization'] );
        if (!$response instanceof StreamResponse){
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($response['errmsg']));
        }
        $filename = UUIDUtils::uuidWithoutSeparator().".png";
        $imagePath = PathUtils::joins(Yii::getAlias("@public"),"uploads","qrcode");
        $imageUrl = PathUtils::joins("/uploads","qrcode",$filename);
        FileHelper::createDirectory($imagePath,777,true);
        $response->saveAs($imagePath,$filename);
        $model = parent::getModelById($companyId);
        if (empty($model)){
            $model = new CompanyInviteCode();
        }
        $model->company_id = $companyId;
        $model->alliance_invite_image = $imageUrl;
        BExceptionAssert::assertTrue($model->save(),$validateException->updateMessage(Common::getModelErrors($model)));
    }



}