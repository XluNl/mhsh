<?php

namespace business\models;

use yii\base\Model;


class UploadPicForm extends Model
{
    public $img;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['img'], 'file','extensions' => 'jpg,png,jpeg','maxSize'=>2048000,'wrongExtension'=>'只支持jpg,png,jpeg三种格式','tooBig'=>'文件大小限制为2M'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'img' => '图片',
        ];
    }
}
