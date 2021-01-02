<?php

namespace template\services;

use common\components\Fish;
use common\models\User;
use template\utils\ExceptionAssert;
use template\utils\StatusCode;
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
        $res = Yii::$app->wechat->miniProgram->auth->session($code);
        ExceptionAssert::assertNotEmpty($res,StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_LOGIN_ERROR));
        ExceptionAssert::assertTrue(key_exists("openid",$res)&&!empty($res['openid']),StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_LOGIN_ERROR));
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
            $model->username = "OP_" . Fish::random("mix", 8);
            $model->salt = Fish::random("mix", 10);
            $model->password = Yii::$app->getSecurity()->generatePasswordHash($model->username . '' . $model->salt);
            $model->headimgurl= $headImgUrl;
            $model->sex = $sex;
            $model->nickname = $nickname;
            $model->user_type = User::USER_TYPE_OFFICIAL;
            $model->generateAccessToken();
            ExceptionAssert::assertTrue($model->save(),StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_CREATE_ERROR));
        }
        else{
            $model->headimgurl= $headImgUrl;
            $model->nickname = $nickname;
            $model->sex = $sex;
            $model->generateAccessToken();
            ExceptionAssert::assertTrue($model->save(),StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_LOGIN_ERROR));
        }
        return ['openid'=>$openid,'session_key'=>$session_key,'token'=>$model->access_token];
    }

}