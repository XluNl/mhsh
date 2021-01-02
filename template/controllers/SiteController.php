<?php
namespace template\controllers;

use template\components\FController;

class SiteController extends FController {
    public $enableCsrfValidation = false;
	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

}
