<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%storage_delivery_out}}".
 *
 * @property int $id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string $trade_no 流水号
 * @property int $operator_id 操作人ID
 * @property string $operator_name 操作人
 * @property string|null $order_goods_ids 本次操作的子单列表
 * @property string|null $storage_sku_statistic 本次发货汇总
 * @property int $status 1待确认  2已确认
 */
class StorageDeliveryOut extends \yii\db\ActiveRecord
{

    const STATUS_UN_CHECK = 1;
    const STATUS_CHECKED = 2;

    public $statusArr=[
        self::STATUS_UN_CHECK=>'待确认',
        self::STATUS_CHECKED=>'已确认',
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%storage_delivery_out}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['trade_no', 'operator_id', 'operator_name'], 'required'],
            [['operator_id', 'status'], 'integer'],
            [['order_goods_ids', 'storage_sku_statistic'], 'string'],
            [['trade_no'], 'string', 'max' => 50],
            [['operator_name'], 'string', 'max' => 255],
            [['trade_no'], 'unique', 'targetAttribute' => ['trade_no'], 'message' => '流水号重复处理'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'trade_no' => '流水号',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人',
            'order_goods_ids' => '本次操作的子单列表',
            'storage_sku_statistic' => '本次发货汇总',
            'status' => '处理状态',
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
