<?php

namespace official\services;

use common\components\Fish;
use common\models\User;
use official\utils\ExceptionAssert;
use official\utils\StatusCode;
use Yii;

class AccountService
{
    /**
     * 登录
     * @param $openid
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function login($openid){
        $res = Yii::$app->officialWechat->app->user->get($openid);
        ExceptionAssert::assertNotEmpty($res,StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_LOGIN_ERROR));
        ExceptionAssert::assertTrue(key_exists("openid",$res)&&!empty($res['openid']),StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_LOGIN_ERROR));
        $openid = $res['openid'];
        $unionid = key_exists('unionid',$res)?$res['unionid']:null;
        $headImgUrl = $res['headimgurl'];
        $nickname = $res['nickname'];
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
        return true;
    }

}