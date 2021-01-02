<?php


namespace backend\models\forms;

use yii\base\Model;

/**
 * Class DeliveryTagForm
 * @package backend\models\forms
 * @property integer $delivery_id
 * @property object $delivery
 * @property integer $tag_info_id
 * @property string $tag_name
 * @property string $tag_value
 */
class DeliveryTagForm extends Model
{
    public $delivery_id;
    public $delivery;
    public $tag_info_id;
    public $tag_name;
    public $tag_value;

    public function rules()
    {
        return [
            [['delivery_id','tag_value'],'required'],
            [['tag_name','delivery','tag_info_id'],'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'delivery_id' => '配送团长',
            'tag_name' => '名称',
            'tag_value' => '数值',
        ];
    }

}