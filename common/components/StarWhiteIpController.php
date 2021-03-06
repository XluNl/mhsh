<?php

namespace common\components;
use common\utils\StringUtils;
use Yii;
use yii\web\Controller;

class StarWhiteIpController extends Controller {

    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            $ip = Yii::$app->request->getRemoteIP();
            if (StringUtils::isBlank($ip)){
                return false;
            }
            $whiteIpList = Yii::$app->params['star.service.info.white.ip.list'];
            if (empty($whiteIpList)){
                return true;
            }
            return in_array($ip,$whiteIpList);
        }
        return false;
    }
}