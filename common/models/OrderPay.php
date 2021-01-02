<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_pay}}".
 *
 * @property integer $id
 * @property string $order_no
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
 * @property string $created_at
 * @property string $updated_at
 * @property integer $version
 * @property integer $remain_fee
 * @property integer $company_id
 */
class OrderPay extends \yii\db\ActiveRecord
{
    public static $UN_KNOWN_COMPANY = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_pay}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['total_fee', 'settlement_total_fee','version','remain_fee','company_id'], 'integer'],
            [['order_no', 'out_trade_no', 'transaction_id', 'nonce_str', 'sign', 'trade_type'], 'string', 'max' => 32],
            [['attach'], 'string', 'max' => 255],
            [['bank_type'], 'string', 'max' => 50],
            [['openid'], 'string', 'max' => 128],
            [['time_end'], 'string', 'max' => 20],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'out_trade_no' => 'Out Trade No',
            'transaction_id' => 'Transaction ID',
            'attach' => 'Attach',
            'total_fee' => 'Total Fee',
            'settlement_total_fee' => 'Settlement Total Fee',
            'bank_type' => 'Bank Type',
            'openid' => 'Openid',
            'nonce_str' => 'Nonce Str',
            'time_end' => 'Time End',
            'sign' => 'Sign',
            'trade_type' => 'Trade Type',
            'version'=>'版本号',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'remain_fee'=>'剩余可退金额',
            'company_id'=>'公司ID',
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
