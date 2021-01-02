<?php


namespace frontend\services;


use common\components\Fish;
use common\models\Captcha;
use common\models\User;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\StatusCode;
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
        $res = Yii::$app->frontendWechat->miniProgram->auth->session($code);
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
            $model->username = "SP_" . Fish::random("mix", 8);
            $model->salt = Fish::random("mix", 10);
            $model->password = Yii::$app->getSecurity()->generatePasswordHash($model->username . '' . $model->salt);
            $model->headimgurl= $headImgUrl;
            $model->sex = $sex;
            $model->nickname = $nickname;
            $model->user_type = User::USER_TYPE_CUSTOMER;
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
     * 用户注册
     * @param $phone
     * @param $captcha
     * @param $name
     * @param $headImageUrl
     * @param $inviteCode
     * @throws \frontend\utils\exceptions\BusinessException
     * @throws \yii\db\Exception
     */
    public static function reg($phone,$captcha,$name,$headImageUrl,$inviteCode){
        $userModel = FrontendCommon::requiredUserModel();
        ExceptionAssert::assertNull($userModel->user_info_id,StatusCode::createExp(StatusCode::ACCOUNT_CREATE_REPEAT));
        CaptchaService::checkCaptcha($phone,$captcha,Captcha::SORT_SMS_CUSTOMER);
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $customer = UserInfoService::register($phone,$name,$headImageUrl,$userModel);
            CustomerInvitationService::bindInvitation($customer['id'],$inviteCode);
            CustomerInvitationLevelService::create($customer['id']);
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,$e->getMessage()));
        }
    }


    public static function autoReg($session, $iv, $encryptedData,$phone,$name,$headImageUrl,$inviteCode){
        $userModel = FrontendCommon::requiredUserModel();
        ExceptionAssert::assertNull($userModel->user_info_id,StatusCode::createExp(StatusCode::ACCOUNT_CREATE_REPEAT));
        $decryptPhone = self::decryptPurePhoneNumber($session, $iv, $encryptedData);
        ExceptionAssert::assertTrue($decryptPhone==$phone,StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'手机号不一致'));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $customer = UserInfoService::register($phone,$name,$headImageUrl,$userModel);
            CustomerInvitationService::bindInvitation($customer['id'],$inviteCode);
            CustomerInvitationLevelService::create($customer['id']);
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,$e->getMessage()));
        }
    }



    public static function decryptPhone($session, $iv, $encryptedData){
        try {
            return Yii::$app->frontendWechat->miniProgram->encryptor->decryptData($session, $iv, $encryptedData);
        }
        catch (\Exception $e){
            ExceptionAssert::assertTrue(false,StatusCode::createExp(StatusCode::PHONE_DECRYPT_ERROR));
        }
        return null;
    }

    public static function decryptPurePhoneNumber($session, $iv, $encryptedData){
        $res=self::decryptPhone($session, $iv, $encryptedData);
        ExceptionAssert::assertNotNull($res,StatusCode::createExp(StatusCode::PHONE_DECRYPT_ERROR));
        ExceptionAssert::assertTrue(key_exists('purePhoneNumber',$res),StatusCode::createExp(StatusCode::PHONE_DECRYPT_ERROR));
        ExceptionAssert::assertNotNull($res['purePhoneNumber'],StatusCode::createExp(StatusCode::PHONE_DECRYPT_ERROR));
        return $res['purePhoneNumber'];
    }


    public static function bindInvitation($customerId,$inviteCode){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            CustomerInvitationService::bindInvitation($customerId,$inviteCode);
            CustomerInvitationLevelService::create($customerId);
            $transaction->commit();
        }
        catch (BusinessException $e){
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,$e->getMessage()));
        }
    }




}