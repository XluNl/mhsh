<?php
namespace common\models;

use yii\base\Model;

class Upload extends Model {

	public $file;
	public $operate;

	public function rules() {
		return [
			[['operate'], 'required'],
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xls, xlsx, zip','maxSize'=>20480000,'tooBig'=>'文件大小限制为20M'],
		];
	}

	public function attributeLabels() {
		return [
			'file' => '选择文件',
			'operate' => '操作类型',
		];
	}
}