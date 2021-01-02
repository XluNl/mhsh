<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%star_exchange_log}}".
 *
 * @property int $id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string $trade_no 交易流水号
 * @property string $exchange_time 业务创建时间
 * @property string $phone 兑换手机号
 * @property int $amount 兑换金额
 * @property int $biz_type 兑换类型
 * @property int|null $biz_id 兑换业务对象id
 * @property int|null $balance_id 兑换业务对象账户id
 * @property int|null $balance_log_id 兑换业务对象账户日志id
 */
class StarExchangeLog extends \yii\db\ActiveRecord
{

    const BIZ_TYPE_CUSTOMER_BALANCE = 1;

    public static $bizTypeArr = [
        self::BIZ_TYPE_CUSTOMER_BALANCE=>'用户消费余额',
    ];

    public static $bizTypeCssArr = [
        self::BIZ_TYPE_CUSTOMER_BALANCE=>'label label-info',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%star_exchange_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'exchange_time'], 'safe'],
            [['exchange_time'], 'required'],
            [['amount', 'biz_type', 'biz_id', 'balance_id', 'balance_log_id'], 'integer'],
            [['trade_no'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 50],
            [['trade_no'], 'unique','message'=>'交易流水号已处理成功'],
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
            'trade_no' => '交易流水号',
            'exchange_time' => '业务创建时间',
            'phone' => '兑换手机号',
            'amount' => '兑换金额',
            'biz_type' => '兑换类型',
            'biz_id' => '兑换业务对象id',
            'balance_id' => '兑换业务对象账户id',
            'balance_log_id' => '兑换业务对象账户日志id',
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
