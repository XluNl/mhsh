<?php
namespace backend\models;

use yii\base\Model;

/**
 * Signup form
 */
class MasterForm extends Model
{
    public $storage_id;
    public $restaurant_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['storage_id', 'filter', 'filter' => 'trim'],
            ['storage_id', 'required'],
            
            ['restaurant_id', 'filter', 'filter' => 'trim'],
            ['restaurant_id', 'required'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'storage_id' => '仓库ID',
            'restaurant_id' => '商户ID',
        ];
    }
}
