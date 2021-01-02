<?php


namespace frontend\services;


use common\components\Fish;

class NoticeService
{
    public static function orderStatusNotice($orderNo,$openId){
        $base_url = \Yii::$app->params['api_url'];
        $url = $base_url."/order/order?order_no=" . $orderNo . "&openid=" . $openId;
        Fish::httpGet($url);
    }
}