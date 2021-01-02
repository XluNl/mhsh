<?php

namespace template\components;
use yii\web\Controller;

class FController extends Controller {

	public function beforeAction($action) {
	    return true;
	}
}