<?php
namespace common\models;
use common\utils\StringUtils;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

class Common {

    public static function getHost(){
        $baseUrl = \Yii::$app->request->hostInfo;
        $baseUrl = str_replace("https://","",$baseUrl);
        $baseUrl = str_replace("http://","",$baseUrl);
        return $baseUrl;
    }

    public static function getInitCompanyId(){
        return \Yii::$app->params['option.init.companyId'];
    }


	public static function get_random($length = 10, $char = 'S') {
		$charStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$max = strlen($charStr);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= $charStr[mt_rand(0, $max - 1)];
		}
		return $string;
	}

	public static function showAmount($num) {
		return $num / 1000;
	}
    public static function setAmount($num) {
        return $num * 1000;
    }

    public static function showPercent($num) {
        return $num / 100;
    }

    public static function setPercent($num) {
        return $num * 100;
    }

    public static function showPercentWithUnit($num) {
        return ($num / 100).'%';
    }

    public static function calcPercent($numerator,$denominator){
        return round($numerator*10000/$denominator)/100.0;
    }
    public static function calcPercentInt($numerator,$denominator){
        return round($numerator*100/$denominator);
    }

    public static function calcPercentWithUnit($numerator,$denominator){
        return self::calcPercent($numerator,$denominator).'%';
    }

    public static function showTenThousandth($num) {
        return $num / 10000;
    }

    public static function setTenThousandth($num) {
        return $num * 10000;
    }


    public static function showAmountWithYuan($num) {
        return ($num / 1000).'元';
    }
	public static function showClerkItemPoint($num) {
	    return round($num,4);
	}


    public static function multiplyWithYuan($num,$coefficient) {
	    if (StringUtils::isBlank($num)){
	        return $num;
        }
        $number = str_replace("元","",$num);
        return ($number*$coefficient).'元';
    }


    public static function isNotSuperCompany($companyId){
        return !self::isSuperCompany($companyId);
    }


    public static function isSuperCompany($companyId){
        $initCompanyId = \Yii::$app->params['option.init.companyId'];
        if ($companyId!=$initCompanyId){
            return false;
        }
        return true;
    }
	

	public static function  skip($message, $action, $params = array(), $kind = "success", $turl = false) {
		if (!empty($message)) {
			Yii::$app->session->setFlash('message', $message);
		}
		if (!empty($action)) {
			if (!$turl) {
				$params[0] = $action;
				$url = Url::toRoute($params);
			} else {
				$url = $action;
			}
			Yii::$app->session->setFlash('url', $url);
		}
        Yii::$app->response->redirect(Url::toRoute('/tips/' . $kind), 301)->send();
        return Yii::$app->end();
	}




    /**
     * 生成绝对地址
     * @param $url
     * @return string
     */
    public static function generateAbsoluteUrl($url){
        return Yii::$app->fileDomain->generateUrl($url);
    }

    /**
     * 移除绝对地址
     * @param $url
     * @return mixed
     */
    public static function removeAbsoluteUrl($url){
        return Yii::$app->fileDomain->removeUrl($url);
    }

    /**
     * 获取默认图片
     * @return string
     */
    public static function getDefaultImageUrl(){
        return self::generateAbsoluteUrl('upload/kongbai.jpg');
    }

    /**
     * @param $model ActiveRecord | \yii\base\Model
     * @return string
     */
    public static function getModelErrors($model){
        if ($model->validate()){
            return "";
        }
        $errors = $model->errors;
        $errMsg = "";
        foreach ($errors as $error){
            foreach ($error as $e){
                $errMsg = $errMsg.$e.'。';
            }

        }
        return $errMsg;
    }

    /**
     * @param $model
     * @return string
     */
    public static function getExistModelErrors($model){
        $errors = $model->errors;
        $errMsg = "";
        foreach ($errors as $error){
            foreach ($error as $e){
                $errMsg = $errMsg.$e.'。';
            }

        }
        return $errMsg;
    }


    public static function getModelValueFromFormData($className,$key='id'){
        $data = Yii::$app->request->post($className);
        if (!StringUtils::isEmpty($data)&&key_exists($key,$data)){
            return $data[$key];
        }
        return null;
    }



}