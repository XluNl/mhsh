<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_balance_pay}}".
 *
 * @property integer $id
 * @property integer $balance_item_id
 * @property integer $customer_id
 * @property string $out_trade_no
 * @property string $transaction_id
 * @property string $attach
 * @property integer $total_fee
 * @property integer $settlement_total_fee
 * @property string $bank_type
 * @property string $openid
 * @property string $nonce_str
 * @property string $time_end
 * @property string $sign
 * @property string $trade_type
 * @property string $type
 * @property integer $company_id
 * @property string $created_at
 */
class CustomerBalancePay extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_balance_pay}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['balance_item_id', 'customer_id', 'total_fee', 'settlement_total_fee', 'company_id'], 'integer'],
            [['created_at'], 'safe'],
            [['out_trade_no', 'transaction_id', 'nonce_str', 'sign', 'trade_type'], 'string', 'max' => 32],
            [['attach'], 'string', 'max' => 255],
            [['bank_type'], 'string', 'max' => 50],
            [['openid'], 'string', 'max' => 128],
            [['time_end', 'type'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'balance_item_id' => '余额操作日志ID',
            'customer_id' => '客户ID',
            'out_trade_no' => '外部交易号',
            'transaction_id' => '微信唯一交易号',
            'attach' => '充值关联号',
            'total_fee' => 'Total Fee',
            'settlement_total_fee' => 'Settlement Total Fee',
            'bank_type' => '银行类型',
            'openid' => '用户标识',
            'nonce_str' => 'Nonce Str',
            'time_end' => 'Time End',
            'sign' => 'Sign',
            'trade_type' => 'Trade Type',
            'type' => 'Type',
            'company_id' => 'Company ID',
            'created_at' => '创建时间',
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
