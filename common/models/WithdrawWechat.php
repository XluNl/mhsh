<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%withdraw_wechat}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $withdraw_apply_id
 * @property string $partner_trade_no
 * @property string $openid
 * @property string $re_user_name
 * @property integer $amount
 * @property string $desc
 * @property string $spbill_create_ip
 * @property string $payment_no
 * @property string $payment_time
 * @property integer $status
 */
class WithdrawWechat extends \yii\db\ActiveRecord
{

    const STATUS_UN_DEAL = 0;
    const STATUS_DEAL_SUCCESS = 1;
    const STATUS_DEAL_FAILED = 2;

    public static $statusArr=[
        self::STATUS_UN_DEAL=>'未打款',
        self::STATUS_DEAL_SUCCESS=>'打款成功',
        self::STATUS_DEAL_FAILED=>'打款失败',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%withdraw_wechat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['withdraw_apply_id', 'partner_trade_no', 'openid'], 'required'],
            [['withdraw_apply_id', 'amount',  'status'], 'integer'],
            [['partner_trade_no', 'openid', 'payment_no','re_user_name'], 'string', 'max' => 64],
            [['desc'], 'string', 'max' => 100],
            [['spbill_create_ip', 'payment_time'], 'string', 'max' => 32],
            [['partner_trade_no'], 'unique'],
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
            'withdraw_apply_id' => '申请ID',
            'partner_trade_no' => '商户订单号',
            'openid' => '用户openid',
            're_user_name' => '收款用户姓名',
            'amount' => '提现金额',
            'desc' => '企业付款备注',
            'spbill_create_ip' => 'Ip地址',
            'payment_no' => '企业付款成功，返回的微信付款单号',
            'payment_time' => '付款成功时间',
            'retry_count' => '重试次数',
            'status' => '处理状态 0未处理 1处理成功 2处理失败',
        ];
    }

    public function generateNo() {
        $yCode = ['WA', 'WB', 'WC', 'WD', 'WE', 'WF', 'WG', 'WH', 'WI', 'WJ'];
        $orderSn = $yCode[intval(date('Y')) - 2019] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%04d', rand(0, 9999));
        return $orderSn;
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
