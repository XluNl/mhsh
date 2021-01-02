<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_balance_item}}".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $amount
 * @property integer $remain_amount
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $in_out
 * @property integer $status
 * @property integer $biz_type
 * @property string $biz_code
 * @property string $biz_data
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 * @property integer $action
 */
class CustomerBalanceItem extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;

    const BIZ_TYPE_ORDER_PAY = 1;
    const BIZ_TYPE_ORDER_REFUND = 2;
    const BIZ_TYPE_CUSTOMER_CHARGE = 3;
    const BIZ_TYPE_CUSTOMER_WITHDRAW  = 4;
    const BIZ_TYPE_ORDER_COMPLETE = 5;
    const BIZ_TYPE_STAR_EXCHANGE = 6;
    const BIZ_TYPE_GROUP_ROOM_REFUND_GAP = 7;
    const BIZ_TYPE_ADD_BONUS = 8;

    public static $bizTypeArr=[
        self::BIZ_TYPE_ORDER_PAY=>'订单支付',
        self::BIZ_TYPE_ORDER_REFUND=>'订单退款',
        self::BIZ_TYPE_CUSTOMER_CHARGE=>'用户充值',
        self::BIZ_TYPE_CUSTOMER_WITHDRAW=>'用户提现',
        self::BIZ_TYPE_ORDER_COMPLETE=>'订单多退少补',
        self::BIZ_TYPE_STAR_EXCHANGE=>'星球币兑换余额',
        self::BIZ_TYPE_GROUP_ROOM_REFUND_GAP=>'拼团退款差价',
        self::BIZ_TYPE_ADD_BONUS=>'奖励金发放',
    ];

    const IN_OUT_IN  = 1;
    const IN_OUT_OUT  = 2;

    public static $inOutArr=[
        self::IN_OUT_IN=>'收入',
        self::IN_OUT_OUT=>'支出',
    ];

    const ACTION_APPLY = 1;
    const ACTION_ACCEPT = 2;
    const ACTION_DENY = 3;

    public static $actionArr=[
        self::ACTION_APPLY=>'处理中',
        self::ACTION_ACCEPT=>'处理成功',
        self::ACTION_DENY=>'处理失败',
    ];
    public static $actionCssArr=[
        self::ACTION_APPLY=>'label label-info',
        self::ACTION_ACCEPT=>'label label-success',
        self::ACTION_DENY=>'label label-danger',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_balance_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'in_out', 'biz_type'], 'required'],
            [['customer_id', 'amount', 'remain_amount', 'operator_id', 'in_out', 'status', 'biz_type','action'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['operator_name'], 'string', 'max' => 100],
            [['biz_code', 'remark'], 'string', 'max' => 255],
            [['biz_data'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => '客户ID',
            'amount' => '操作金额',
            'remain_amount' => '操作后余额',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人名称',
            'in_out' => '收入或支出',
            'status' => '状态',
            'biz_type' => '日志类型',
            'biz_code' => '业务编号',
            'biz_data' => '业务数据',
            'action'=>'操作',
            'remark' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
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
