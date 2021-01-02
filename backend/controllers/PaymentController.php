<?php

namespace backend\controllers;
use backend\models\BackendCommon;
use common\models\Order;
use common\models\OrderPay;
use common\models\Payment2;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

class PaymentController extends BaseController {

	public function actionList() {
	    $company_id = \Yii::$app->user->identity->company_id;
		$payment_id = Yii::$app->request->get("payment_id");
        $initCompanyId = \Yii::$app->params['option.init.companyId'];
		$existPayCategorys = ArrayHelper::getColumn(Payment2::find()->select('pay_category')->Where(['company_id'=>$company_id])->all(),'pay_category');
		$payments = Payment2::find()->Where([
            'OR',
            ['company_id'=>$company_id],
            [
                'AND',
                ['company_id'=>$initCompanyId],
                ['not in','pay_category',$existPayCategorys]
            ]
        ])
			->orderBy("pay_category desc")
			->all();
		if (empty($payment_id)) {
			$model = $payments[0];
		} else {
			$model = Payment2::find()->where(['id'=>$payment_id,'company_id'=>[$company_id,$initCompanyId]])->orderBy('company_id desc')->one();
			if (empty($model)){
			    return $this->redirect(Url::toRoute(["list"]));
            }
		}
		$params = array("payments" => $payments, "model" => $model);
		return $this->render("list", $params);
	}
	public function actionModify() {
	    $company_id = \Yii::$app->user->identity->company_id;
        $initCompanyId = \Yii::$app->params['option.init.companyId'];
		$payment_id = Yii::$app->request->get("payment_id",0);
        $model = Payment2::findOne(['id'=>$payment_id,'company_id'=>$company_id]);
		if (empty($model)) {
            $defaultModel = Payment2::findOne(['id'=>$payment_id,'company_id'=>$initCompanyId]);
			$model = new Payment2(array('scenario' => 'add'));
			if (!empty($defaultModel)){
                $model->company_id = $company_id;
                $model->pay_name = $defaultModel->pay_name;
                $model->pay_type = $defaultModel->pay_type;
                $model->pay_status = $defaultModel->pay_status;
                $model->pay_describe = $defaultModel->pay_describe;
                $model->pay_category = $defaultModel->pay_category;
            }
		} else {
			$model->scenario = "modify";
		}
		if (Yii::$app->request->isPost) {
			/*$data = $_POST["Payment"];
			$model->pay_describe = $data["pay_describe"];
            $model->pay_account = $data["pay_account"];
			$model->pay_class = $data["pay_class"];*/
			if ($model->load(Yii::$app->request->post()) && $model->validate()) {
				if ($model->save(false)) {
					return $this->redirect(Url::toRoute(array('payment/list', 'payment_id' => $model->id)));
				}
			}
		}
		$params = array("model" => $model);
		return $this->render("modify", $params);
	}

	public function actionStatus() {
	    $company_id = \Yii::$app->user->identity->company_id;
        $initCompanyId = \Yii::$app->params['option.init.companyId'];
		$payment_id = Yii::$app->request->get("payment_id",0);
		$pay_status = Yii::$app->request->get("pay_status", 0);
        $flag = true;
        $existModel = Payment2::find()->Where(['id'=>$payment_id,'company_id'=>$company_id])->one();
        Yii::error("xxxxx",Json::encode($existModel));
        if (!empty($existModel)){
            $existModel->pay_status = $pay_status;
            if (!$existModel->save()){
                $flag = false;
            }
        }
        else{
            $existModel = Payment2::find()->Where(['id'=>$payment_id,'company_id'=>$initCompanyId])->one();
            $existModel->id = null;
            $existModel->isNewRecord = true;
            $existModel->pay_status = $pay_status;
            $existModel->company_id = $company_id;
            Yii::error("yyyyyy",$existModel);
            if (!$existModel->save()){
                $flag = false;
            }
            else{
                $payment_id = $existModel->id;
            }
        }
        if ($flag) {
            BackendCommon::skip('操作成功','payment/list',['payment_id' => $payment_id],'success');
        } else {
            BackendCommon::skip('操作失败','payment/list',['payment_id' => $payment_id],'error');
        }
	}
	
	public function actionWxlist() {
	    $company_id = \Yii::$app->user->identity->company_id;
	    $query = OrderPay::find()->Where([ OrderPay::tableName().'.company_id'=>$company_id]);
	    $keyword = Yii::$app->request->get("keyword");
	    if (!empty($keyword)) {
	        $query = $query->andFilterWhere(['or',['like', OrderPay::tableName().'.order_no', $keyword],['like', OrderPay::tableName().'.transaction_id', $keyword]]);
	    }
	    $countQuery = clone $query;
	    $pages = new Pagination(['totalCount' => $countQuery->count()]);
	    $pages->pageSize = 25;
	    $models = $query->select(OrderPay::tableName().'.*,'.Order::tableName().'.create_time,'.Order::tableName().'.accept_restaurant,'.Order::tableName().'.accept_name,'.Order::tableName().'.accept_restaurant,')->offset($pages->offset)
	    ->limit($pages->limit)->leftJoin(Order::tableName(),Order::tableName().'.order_no='.OrderPay::tableName().'.order_no')->orderBy(Order::tableName().'.create_time desc')->asArray()
	    ->all();
	    return $this->render('wxlist', array('models' => $models, 'pages' => $pages,'keyword'=>$keyword));
	}
}