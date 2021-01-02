<?php


namespace backend\models\forms;


use yii\base\Model;

/**
 *  SystemOptionsModifyForm
 *
 * @property string $option_field
 * @property string $option_value
 */
class SystemOptionsModifyForm extends Model
{
    public $option_field;
    public $option_value;

    public function rules()
    {
        return [
            [['option_field','option_value'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'option_field' => '配置项名称',
            'option_value' => '配置项内容',
        ];
    }
}