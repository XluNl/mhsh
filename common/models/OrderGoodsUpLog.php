<?php

namespace common\models;

/**
 * This is the model class for table "{{%order_goods_up_log}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $username
 * @property integer $restaurant_id
 * @property integer $storage_id
 * @property integer $goods_id
 * @property double $num
 * @property string $add_time
 * @property boolean $status
 */
class OrderGoodsUpLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_goods_up_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'restaurant_id', 'storage_id', 'goods_id'], 'integer'],
            [['num'], 'number'],
            [['add_time'], 'safe'],
            [['status'], 'boolean'],
            [['username'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'username' => 'Username',
            'restaurant_id' => 'Restaurant ID',
            'storage_id' => 'Storage ID',
            'goods_id' => 'Goods ID',
            'num' => 'Num',
            'add_time' => 'Add Time',
            'status' => 'Status',
        ];
    }
}
