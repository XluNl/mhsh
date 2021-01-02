<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%wechat_pay_refund_log}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property string $transaction_id
 * @property integer $biz_type
 * @property string $biz_id
 * @property string $out_trade_no
 * @property string $refund_id
 * @property string $out_refund_no
 * @property integer $total_fee
 * @property integer $refund_fee
 * @property string $refund_status
 * @property string $success_time
 */
class WechatPayRefundLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_pay_refund_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'success_time'], 'safe'],
            [['company_id', 'biz_type', 'total_fee', 'refund_fee'], 'integer'],
            [['transaction_id', 'biz_type', 'biz_id', 'out_trade_no', 'out_refund_no', 'total_fee', 'refund_fee'], 'required'],
            [['transaction_id', 'biz_id', 'out_trade_no', 'out_refund_no', 'refund_status'], 'string', 'max' => 32],
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
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => '公司ID',
            'transaction_id' => '微信订单号',
            'biz_type' => '支付单类型',
            'biz_id' => '支付单业务编号',
            'out_trade_no' => '商户订单号',
            'refund_id' => '微信退款单号',
            'out_refund_no' => '商户退款单号',
            'total_fee' => '原支付总金额',
            'refund_fee' => '退款金额',
            'refund_status' => '退款状态',
            'success_time' => '回调时间',
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
