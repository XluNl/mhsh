<?php

namespace common\models;
use frontend\models\FrontendCommon;
use yii\db\ActiveRecord;

class Options extends ActiveRecord {

	public static function tableName() {
		return "{{%system_options}}";
	}

	public function rules() {
		return [
			//[['id', 'option_name', 'option_field', 'option_value'], 'save'],
			[['option_name', 'option_field', 'option_value'], 'required', 'on' => 'update-option'],
		];
	}

	public function attributeLabels() {
		return [
			'option_name' => '选项名称',
			'option_field' => '选项字段',
			'option_value' => '选项值',
			'created_at' => '创建时间',
		];
	}

	public static function get_option($filed = "") {
	    $company_id = FrontendCommon::requiredFCompanyId();
	    $initCompanyId = \Yii::$app->params['option.init.companyId'];
		$row = Options::find()->where(['option_field' => $filed,'company_id'=>[$company_id,$initCompanyId]])->orderBy("company_id desc")->one();
		return (empty($row)) ? "" : $row->option_value;
	}
	
	public static function get_option_with_default($filed,$default= "") {
	    $company_id = FrontendCommon::requiredFCompanyId();
	    $initCompanyId = \Yii::$app->params['option.init.companyId'];
	    $row = Options::find()->where(['option_field' => $filed,'company_id'=>[$company_id,$initCompanyId]])->orderBy("company_id desc")->one();
	    return (empty($row)) ? $default : $row->option_value;
	}

    public static function get_option_with_default_with_company_id($filed,$company_id,$default= "") {
        $initCompanyId = \Yii::$app->params['option.init.companyId'];
        $row = Options::find()->where(['option_field' => $filed,'company_id'=>[$company_id,$initCompanyId]])->orderBy("company_id desc")->one();
        return (empty($row)) ? $default : $row->option_value;
    }
}