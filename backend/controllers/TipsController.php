<?php

namespace backend\controllers;
use Yii;
use yii\web\Controller;

/**
 *   错误跳转控制器
 */
class TipsController extends Controller {

	public function actionSuccess() {
		$message = (Yii::$app->session->hasFlash('message')) ? Yii::$app->session->getFlash('message') : "";
		$url = (Yii::$app->session->hasFlash('url')) ? Yii::$app->session->getFlash('url') : 'site/index';
		$params = ['message' => $message, 'url' => $url];
		return $this->render("success", $params);
	}

	public function actionError() {
		$message = (Yii::$app->session->hasFlash('message')) ? Yii::$app->session->getFlash('message') : "";
		$url = (Yii::$app->session->hasFlash('url')) ? Yii::$app->session->getFlash('url') : 'site/index';
		$params = ['message' => $message, 'url' => $url];
		return $this->render("error", $params);
	}
}