<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_customer_service_log}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property integer $customer_service_id
 * @property integer $action
 * @property integer $operator_id
 * @property string $operator_name
 * @property string $remark
 */
class OrderCustomerServiceLog extends \yii\db\ActiveRecord
{
    const ACTION_APPLY = 1;
    const ACTION_ACCEPT_DELIVERY = 2;
    const ACTION_DENY_DELIVERY = 3;
    const ACTION_CANCEL = 4;
    const ACTION_ACCEPT_AGENT = 5;
    const ACTION_DENY_AGENT = 6;
    public static $actionArr=[
        self::ACTION_APPLY => '申请售后',
        self::ACTION_ACCEPT_DELIVERY => '团长/联盟商审核通过',
        self::ACTION_DENY_DELIVERY => '团长/联盟商审核拒绝',
        self::ACTION_CANCEL => '主动撤回',
        self::ACTION_ACCEPT_AGENT => '代理商审核通过',
        self::ACTION_DENY_AGENT => '代理商审核拒绝',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_customer_service_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['customer_service_id', 'action'], 'required'],
            [['customer_service_id', 'action', 'operator_id'], 'integer'],
            [['operator_name', 'remark'], 'string', 'max' => 255],
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
            'action' => '操作类型',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人姓名',
            'remark' => '备注',
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
}
