<?php


namespace alliance\services;


use alliance\models\AllianceCommon;
use alliance\utils\ExceptionAssert;
use alliance\utils\StatusCode;
use common\components\Fish;
use common\models\Captcha;
use common\models\User;
use common\utils\StringUtils;
use Yii;

class AccountService
{
    /**
     * 登录
     * @param $code
     * @param $nickname
     * @param $headImgUrl
     * @param $sex
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \yii\base\Exception
     */
    public static function login($code,$nickname,$headImgUrl,$sex){
        $res = Yii::$app->allianceWechat->miniProgram->auth->session($code);
        ExceptionAssert::assertNotEmpty($res,StatusCode::createExp(StatusCode::MINI_WECHAT_LOGIN_ERROR));
        ExceptionAssert::assertTrue(key_exists("openid",$res)&&!empty($res['openid']),StatusCode::createExp(StatusCode::MINI_WECHAT_LOGIN_ERROR));
        $openid = $res['openid'];
        $unionid = key_exists('unionid',$res)?$res['unionid']:null;
        $session_key = $res['session_key'];
        $model = User::find()->where(["openid" => $openid])->one();
        if (empty($sex)){
            $sex = 0;
        }
        else{
            $sex = (int)$sex;
        }
        if (empty($model)) {
            $model = new User(["scenario" => "create"]);
            $model->openid = $openid;
            $model->unionid = $unionid;
            $model->username = "AL_" . Fish::random("mix", 8);
            $model->salt = Fish::random("mix", 10);
            $model->password = Yii::$app->getSecurity()->generatePasswordHash($model->username . '' . $model->salt);
            $model->headimgurl= $headImgUrl;
            $model->sex = $sex;
            $model->nickname = $nickname;
            $model->user_type = User::USER_TYPE_ALLIANCE;
            $model->generateAccessToken();
            ExceptionAssert::assertTrue($model->save(),StatusCode::createExp(StatusCode::MINI_WECHAT_ACCOUNT_CREATE_ERROR));
        }
        else{
            $model->headimgurl= $headImgUrl;
            if (StringUtils::isNotBlank($unionid)){
                $model->unionid= $unionid;
            }
            if (StringUtils::isNotBlank($nickname)){
                $model->nickname = $nickname;
            }
            $model->sex = $sex;
            $model->generateAccessToken();
            ExceptionAssert::assertTrue($model->save(),StatusCode::createExp(StatusCode::MINI_WECHAT_LOGIN_ERROR));
        }
        return ['openid'=>$openid,'session_key'=>$session_key,'token'=>$model->access_token];
    }

    /**
     * 注册
     * @param $phone
     * @param $captcha
     * @param $name
     * @throws \alliance\utils\exceptions\BusinessException
     * @throws \yii\db\Exception
     */
    public static function reg($phone,$captcha,$name){
        $userModel = AllianceCommon::requiredUserModel();
        ExceptionAssert::assertNull($userModel->user_info_id, StatusCode::createExp(StatusCode::ACCOUNT_CREATE_REPEAT));
        CaptchaService::checkCaptcha($phone,$captcha,Captcha::SORT_SMS_ALLIANCE);
        UserInfoService::register($phone,$name,$userModel);
    }
}