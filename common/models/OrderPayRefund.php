<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_pay_refund}}".
 *
 * @property integer $id
 * @property string $transaction_id
 * @property string $out_trade_no
 * @property string $refund_id
 * @property string $out_refund_no
 * @property integer $total_fee
 * @property integer $refund_fee
 * @property string $created_at
 * @property string $updated_at
 * @property string $refund_status
 * @property string $success_time
 * @property integer $company_id
 */
class OrderPayRefund extends \yii\db\ActiveRecord
{
    const REFUND_STATUS_REFUNDING = 'refunding';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_CHANGE = 'change';
    const REFUND_STATUS_REFUND_CLOSE = 'refund_close';

    public static $refundStatusArr=[
        self::REFUND_STATUS_REFUNDING=>'退款中',
        self::REFUND_STATUS_SUCCESS=>'退款成功',
        self::REFUND_STATUS_CHANGE=>'退款异常',
        self::REFUND_STATUS_REFUND_CLOSE=>'退款关闭',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_pay_refund}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transaction_id', 'out_trade_no', 'out_refund_no', 'total_fee', 'refund_fee'], 'required'],
            [['total_fee', 'refund_fee', 'company_id'], 'integer'],
            [['created_at', 'updated_at', 'success_time'], 'safe'],
            [['out_refund_no'],'unique'],
            [[ 'transaction_id', 'out_trade_no', 'out_refund_no', 'refund_status'], 'string', 'max' => 32],
            [['refund_id'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaction_id' => '微信订单号',
            'out_trade_no' => '商户订单号',
            'refund_id' => '微信退款单号',
            'out_refund_no' => '商户退款单号',
            'total_fee' => '原支付总金额',
            'refund_fee' => '退款金额',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'refund_status' => '退款状态',
            'success_time' => '回调时间',
            'company_id' => '公司ID',
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
