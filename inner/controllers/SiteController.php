<?php
namespace inner\controllers;

use inner\components\InnerControllerInner;

class SiteController extends InnerControllerInner {
    public $enableCsrfValidation = false;
	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

}
