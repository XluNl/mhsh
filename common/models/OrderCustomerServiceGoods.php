<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_customer_service_goods}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property integer $customer_service_id
 * @property integer $order_goods_id
 * @property float $order_goods_num
 * @property integer $order_goods_order_num
 * @property integer $order_goods_order_amount
 * @property integer $order_goods_ac_amount
 */
class OrderCustomerServiceGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_customer_service_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['customer_service_id', 'order_goods_id','order_goods_order_num','order_goods_order_amount','order_goods_ac_amount'], 'integer'],
            [['order_goods_num'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => '创建时间',
            'customer_service_id' => '售后ID',
            'order_goods_id' => '子商品ID',
            'order_goods_num'=>'售后数量',
            'order_goods_order_num'=>'原订单商品数量',
            'order_goods_order_amount'=>'原订单商品金额',
            'order_goods_ac_amount'=>'实际订单商品金额',
        ];
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getOrderGoods() {
        return $this->hasOne(OrderGoods::className(), ['id' => 'order_goods_id']);
    }


    public function getLog() {
        return $this->hasMany(OrderCustomerServiceLog::className(), ['customer_service_id' => 'id']);
    }
}
