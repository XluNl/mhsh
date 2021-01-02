<?php

namespace common\models;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Payment2 extends ActiveRecord {

	public static $pay_type_list = array(
		'0'=>'线下',
		'1'=>'线上'
		);
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
	public static $pay_status_list = array(
		'0'=>'停用',
		'1'=>'启用'
		);
	const CATEGORY_UNKONWN = 0;
    const CATEGORY_OFFLINE = 1;
    const CATEGORY_BANKCARD = 2;
    const CATEGORY_WECHATPAY = 3;
    const CATEGORY_ACCOUNT = 4;
    const CATEGORY_ACCOUNT_WITH_OFFLINE = 5;
    public static $pay_category_list = array(
        '0'=>'未知',
        '1'=>'货到付款',
        '2'=>'银行卡支付',
        '3'=>'微信支付',
        '4'=>'余额支付',
        '5'=>'余额支付,不足部分货到付款'
    );

	public static function tableName() {
		return "{{%payment}}";
	}

	public function attributeLabels() {
		return array(
			'pay_name'=>'支付方式名称',
			'pay_status'=>'支付方式状态',
			'pay_type'=>'支付方式类型',
			'pay_describe'=>'支付方式描述',
			'pay_account'=>'支付收款账户',
			'pay_class'=>'支付调用类',
            'company_id'=>'公司ID',
            'pay_category'=>'支付类别',
		);
	}

	public function rules() {
		return array(
			array(array('pay_name', 'pay_status','pay_type','pay_account'), 'required', 'on' => array('add', 'modify')),
			//array(array('pay_account'), 'validateAccount', 'on' => array('add', 'modify')),
			
		);
	}

	public function validateAccount(){
		if ($this->pay_type == 0) {
			$this->pay_account = '无';
		}else{
			if (empty($this->pay_account)) {
				$this->addError('pay_account',"线上付款必须填写收款账户");
			}
		}
	}

	public function beforeSave($insert){
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->pay_addtime = time();
			}
			return true;
		}else{
			return false;
		}
	}

    public static function getAvailablePayment($select = "*"){
        $company_id = \Yii::$app->user->identity->company_id;
        $initCompanyId = \Yii::$app->params['option.init.companyId'];
        $existPayCategories = ArrayHelper::getColumn(Payment2::find()->select('pay_category')->Where(['company_id'=>$company_id])->all(),'pay_category');
        $payments = Payment2::find()->select($select)->Where([
            'AND',
            ['pay_status'=>Payment2::STATUS_ACTIVE],
            [
                'OR',
                ['company_id'=>$company_id],
                [
                    'AND',
                    ['company_id'=>$initCompanyId],
                    ['not in','pay_category',$existPayCategories]
                ]
            ]
        ])
            ->orderBy("pay_category desc")
            ->asArray()
            ->all();
        return $payments;
    }

    public static function getPaymentByCategory($pay_category,$select = "*"){
        $company_id = \Yii::$app->user->identity->company_id;
        $initCompanyId = \Yii::$app->params['option.init.companyId'];

        $payment = Payment2::find()->select($select)->Where(['pay_category'=>$pay_category,'company_id'=>$company_id])->one();
        if (!empty($payment)){
            if ($payment->pay_status!=Payment2::STATUS_ACTIVE){
                return null;
            }
        }
        else{
            $payment = Payment2::find()->select($select)->Where(['pay_category'=>$pay_category,'company_id'=>$initCompanyId])->one();
            if ($payment->pay_status!=Payment2::STATUS_ACTIVE){
                return null;
            }

        }
        return $payment;
    }
	
}