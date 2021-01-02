<?php


namespace common\services;

use common\components\Fish;
use common\models\Captcha;
use common\utils\StringUtils;
use Yii;

class CaptchaService
{

    /**
     * 发送短信验证码
     * @param $data
     * @param $sort
     * @return array
     */
    public static function sendCaptcha($data,$sort){
        if (!key_exists($sort,Captcha::$sortArr)){
            return [false,'sort参数错误'];
        }
        list($result,$captcha) = self::getCaptcha($data,Captcha::SORT_SMS_BUSINESS);
        if (!$result){
            return [$result,$captcha];
        }
        list($result,$error) = self::sendSMS($data,$captcha,Captcha::SORT_SMS_BUSINESS);
        if (!$result){
            return [$result,$error];
        }
        return [true,$captcha];
    }


    /**
     * 验证码校验
     * @param $data
     * @param $captcha
     * @param $sort
     * @return array
     */
    public static function checkCaptcha($data, $captcha, $sort) {
        if (StringUtils::isBlank($data)){
            return [false,'data参数缺失'];
        }
        if (StringUtils::isBlank($captcha)){
            return [false,'captcha参数缺失'];
        }
        if (StringUtils::isBlank($sort)){
            return [false,'sort参数缺失'];
        }
        $model = Captcha::find()->where(['data' => $data, 'sort' => $sort, 'status' => Captcha::STATUS_VALID])->one();
        if (StringUtils::isEmpty($model)){
            return [false,'请先获取验证码'];
        }
        if ((time() - strtotime($model->created_at)) > Yii::$app->params["captcha_effect_time"]){
            return [false,'验证码已失效'];
        }
        if ($model->fail_num >= Yii::$app->params["captcha_effect_num"]){
            return [false,'验证次数过多，请重新获取'];
        }
        if ($model->code != $captcha) {
            $model->fail_num = $model->fail_num + 1;
            if (!$model->save()){
                return [false,'验证码错误'];
            }
        }
        $model->status = Captcha::STATUS_USED;
        if (!$model->save()){
            return [false,'验证码更新错误'];
        }
        return [true,''];
    }

    /**
     * 获取验证码
     * @param $data
     * @param $sort
     * @return array
     */
    public static function getCaptcha($data, $sort) {
        if (StringUtils::isBlank($data)){
            return [false,'data参数缺失'];
        }
        if (StringUtils::isBlank($sort)){
            return [false,'sort参数缺失'];
        }
        $model = Captcha::find()->where(['data' => $data, 'status' => Captcha::STATUS_VALID])->one();
        if (!empty($model)){
            if (time() - strtotime($model->created_at)<=60){
                return [false,'60秒内不能重复获取'];
            }
            $model->status = Captcha::STATUS_UNUSED;
            if (!$model->save()){
                return [false,'验证码强制失效失败'];
            }
        }
        $code = Fish::random("number", 6);
        $captcha = new Captcha();
        $captcha->data = $data;
        $captcha->code = $code;
        $captcha->sort = $sort;
        if (!$captcha->save()){
            return [false,'验证码保存失败'];
        }
        return [true,$code];
    }

    /**
     * 发送验证码
     * @param $data
     * @param $captcha
     * @param $sort
     * @return array
     */
    public static function sendSMS($data,$captcha,$sort){
        $smsData = [
            'sort' => $sort,
            'tpl_value' => urlencode('#code#') . '=' . urlencode($captcha),
            'mobile' => $data,
        ];
        $resSms = Yii::$app->sms->send($smsData);
        if (StringUtils::isEmpty($resSms)){
            return [false,'发送验证码失败'];
        }
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
        if ($resSms['code'] != 0){
            return [false,$remark];
        }
        return [true,''];
    }

}