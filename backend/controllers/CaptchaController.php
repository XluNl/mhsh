<?php
namespace backend\controllers;

use common\models\Captcha;
use Yii;
use yii\data\ActiveDataProvider;


class CaptchaController extends BaseController {

	public function actionList() {
		$keyword = Yii::$app->request->get("keyword", "");
		$condition = [];
		if (!empty($keyword)) {
			$condition = [
				'OR',
				['like' , 'data' , $keyword],
			];
		}
		$query = Captcha::find()->where($condition);
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 25,
			],
			'sort' => [
				'defaultOrder' => [
					'created_at' => SORT_DESC,
				],
			],
		]);
		$params = ['dataProvider', 'keyword'];
		return $this->render('list', compact($params));
	}
	
}
