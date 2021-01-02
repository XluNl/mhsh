<?php


namespace alliance\services;

use alliance\utils\ExceptionAssert;
use alliance\utils\StatusCode;
use common\components\Fish;
use common\models\Captcha;
use Yii;

class CaptchaService
{
    /**
     * 发送短信验证码
     * @param $data
     * @param $sort
     * @return string
     */
    public static function sendCaptcha($data,$sort){
        ExceptionAssert::assertTrue(key_exists($sort,Captcha::$sortArr),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'sort'));
        $captcha = self::getCaptcha($data,Captcha::SORT_SMS_ALLIANCE);
        self::sendSMS($data,$captcha,Captcha::SORT_SMS_ALLIANCE);
        return $captcha;
    }


    /**
     * 验证码校验
     * @param $data
     * @param $captcha
     * @param $sort
     */
    public static function checkCaptcha($data, $captcha, $sort) {
        ExceptionAssert::assertNotNull($data,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'data'));
        ExceptionAssert::assertNotNull($captcha,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'captcha'));
        ExceptionAssert::assertNotNull($sort,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sort'));
        $model = Captcha::find()->where(['data' => $data, 'sort' => $sort, 'status' => Captcha::STATUS_VALID])->one();
        ExceptionAssert::assertNotEmpty($model,StatusCode::createExpWithParams(StatusCode::CHECK_CAPTCHA_ERROR,'请先获取验证码'));
        ExceptionAssert::assertTrue((time() - strtotime($model->created_at)) <= Yii::$app->params["captcha_effect_time"],StatusCode::createExpWithParams(StatusCode::CHECK_CAPTCHA_ERROR,'验证码已失效'));
        ExceptionAssert::assertTrue($model->fail_num < Yii::$app->params["captcha_effect_num"],StatusCode::createExpWithParams(StatusCode::CHECK_CAPTCHA_ERROR,'验证次数过多，请重新获取'));
        if ($model->code != $captcha) {
            $model->fail_num = $model->fail_num + 1;
            $model->save();
            ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::CHECK_CAPTCHA_ERROR,'验证码更新错误'));
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::CHECK_CAPTCHA_ERROR,'验证码错误'));
        }
        $model->status = Captcha::STATUS_USED;
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::CHECK_CAPTCHA_ERROR,'验证码更新错误'));
    }

    /**
     * 获取验证码
     * @param $data
     * @param $sort
     * @return string
     */
    public static function getCaptcha($data, $sort) {
        ExceptionAssert::assertNotNull($data,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'data'));
        ExceptionAssert::assertNotNull($sort,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sort'));

        $model = Captcha::find()->where(['data' => $data, 'status' => Captcha::STATUS_VALID])->one();
        if (!empty($model)){
            ExceptionAssert::assertTrue(time() - strtotime($model->created_at)>60,StatusCode::createExpWithParams(StatusCode::SEND_CAPTCHA_ERROR,'60秒内不能重复获取'));
            $model->status = Captcha::STATUS_UNUSED;
            ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::SEND_CAPTCHA_ERROR,'验证码强制失效失败'));
        }
        $code = Fish::random("number", 6);
        $captcha = new Captcha();
        $captcha->data = $data;
        $captcha->code = $code;
        $captcha->sort = $sort;
        ExceptionAssert::assertTrue($captcha->save(),StatusCode::createExpWithParams(StatusCode::SEND_CAPTCHA_ERROR,'验证码保存失败'));

        return $code;
    }

    /**
     * 发送验证码
     * @param $data
     * @param $captcha
     * @param $sort
     */
    public static function sendSMS($data,$captcha,$sort){
        $smsData = [
            'sort' => $sort,
            'tpl_value' => urlencode('#code#') . '=' . urlencode($captcha),
            'mobile' => $data,
        ];
        $resSms = Yii::$app->sms->send($smsData);
        ExceptionAssert::assertNotEmpty($resSms,StatusCode::createExpWithParams(StatusCode::SEND_CAPTCHA_ERROR,'发送验证码失败'));
        if (!empty($resSms['msg'])) {
            $remark = $resSms['msg'];
        } elseif (!empty($resSms['detail'])) {
            $remark = $resSms['detail'];
        } else {
            $remark = "验证码发送失败-未知的错误";
        }
        Captcha::updateAll(
            ['recode' => $resSms['code'], 'remark' => $remark],
            ['sort' => $sort,'status'=>Captcha::STATUS_VALID, 'data' => $data, 'code' => $captcha]
        );
        ExceptionAssert::assertTrue($resSms['code'] == 0,StatusCode::createExpWithParams(StatusCode::SEND_CAPTCHA_ERROR,$remark));
    }

}