<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%schedule_out_stock_log}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $batch_id
 * @property string $order_no
 * @property integer $order_goods_id
 * @property integer $num
 * @property integer $operator_id
 * @property string $operator_name
 */
class ScheduleOutStockLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schedule_out_stock_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'batch_id', 'order_goods_id', 'num', 'operator_id'], 'integer'],
            [['batch_id', 'order_no', 'order_goods_id', 'operator_id', 'operator_name'], 'required'],
            [['order_no'], 'string', 'max' => 50],
            [['operator_name'], 'string', 'max' => 255],
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
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'batch_id' => '排期发货批次ID',
            'order_no' => '订单ID',
            'order_goods_id' => '订单子商品ID',
            'num' => '数量',
            'operator_id' => '操作人id',
            'operator_name' => '操作人名称',
        ];
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}
