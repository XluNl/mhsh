<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%wechat_pay_log}}".
 *
 * @property integer $id
 * @property integer $biz_type
 * @property string $biz_id
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
 * @property integer $version
 * @property integer $remain_fee
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 */
class WechatPayLog extends \yii\db\ActiveRecord
{
    const BIZ_TYPE_ALLIANCE_AUTH = 1;
    const BIZ_TYPE_DELIVERY_AUTH = 2;
    const BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY = 3;

    public static $bizTypeArr=[
        self::BIZ_TYPE_ALLIANCE_AUTH=>'联盟保证金',
        self::BIZ_TYPE_DELIVERY_AUTH=>'社区合伙人运营服务费',
        self::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY=>'社区合伙人质保金充值',
    ];

    public static $UN_KNOWN_COMPANY = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_pay_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['biz_type', 'biz_id'], 'required'],
            [['biz_type', 'total_fee', 'settlement_total_fee', 'version', 'remain_fee', 'company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['biz_id', 'out_trade_no', 'transaction_id', 'nonce_str', 'sign', 'trade_type'], 'string', 'max' => 32],
            [['attach'], 'string', 'max' => 255],
            [['bank_type'], 'string', 'max' => 50],
            [['openid'], 'string', 'max' => 128],
            [['time_end'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'biz_type' => '支付单类型',
            'biz_id' => '支付单业务编号',
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
            'version' => '版本号',
            'remain_fee' => '剩余金额',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
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
