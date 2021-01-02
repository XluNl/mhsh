<?php
namespace frontend\models;

use yii\base\Model;

/**
 * SignUp form
 */
class SignUpForm extends Model {

    public $phone;
    public $captcha;
    public $name;

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
		    [['phone','captcha','name'],'required'],
		];
	}

	public function attributeLabels() {
		return [
            'phone' => '手机号',
            'captcha' => '验证码',
            'name' => '姓名',
        ];
	}
}
