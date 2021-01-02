<?php

namespace business\components;
use yii\web\Controller;

class FController extends Controller {

	public function beforeAction($action) {
	    return true;
	}
}