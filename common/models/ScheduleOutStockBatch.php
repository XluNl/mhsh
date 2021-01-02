<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%schedule_out_stock_batch}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $schedule_id
 * @property integer $order_goods_num
 * @property integer $sku_num
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $type
 */
class ScheduleOutStockBatch extends \yii\db\ActiveRecord
{
    const TYPE_SELF = 1;
    const TYPE_HA = 2;
    const TYPE_DELIVERY = 3;

    public static $typeArr=[
        self::TYPE_SELF=>'自营',
        self::TYPE_HA=>'联盟',
        self::TYPE_DELIVERY=>'团长',
    ];

    public static $typeCssArr=[
        self::TYPE_SELF=>'label label-info',
        self::TYPE_HA=>'label label-success',
        self::TYPE_DELIVERY=>'label label-primary',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schedule_out_stock_batch}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'schedule_id', 'order_goods_num', 'sku_num', 'operator_id','type'], 'integer'],
            [['schedule_id', 'operator_id', 'operator_name'], 'required'],
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
            'schedule_id' => '排期ID',
            'order_goods_num' => '订单商品数量',
            'sku_num' => '发货数量',
            'operator_id' => '操作人id',
            'operator_name' => '操作人名称',
            'type'=>'类型',
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
