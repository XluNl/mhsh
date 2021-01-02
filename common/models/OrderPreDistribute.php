<?php

namespace common\models;

/**
 * This is the model class for table "{{%order_pre_distribute}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $order_no
 * @property string $order_amount
 * @property string $order_time
 * @property integer $biz_id
 * @property integer $biz_type
 * @property integer $level
 * @property integer $amount
 * @property integer $amount_ac
 */
class OrderPreDistribute extends \yii\db\ActiveRecord
{

    const BIZ_TYPE_POPULARIZER = 1;
    const BIZ_TYPE_DELIVERY = 2;
    const BIZ_TYPE_AGENT = 3;
    const BIZ_TYPE_ALLIANCE = 4;
    const BIZ_TYPE_CUSTOMER = 5;
    const BIZ_TYPE_PAYMENT_HANDLING_FEE = 7;
    const BIZ_TYPE_COMPANY = 9;

    public static $bizTypeArr = [
        self::BIZ_TYPE_CUSTOMER=>'用户',
        self::BIZ_TYPE_POPULARIZER=>'分享团长',
        self::BIZ_TYPE_DELIVERY=>'配送团长',
        self::BIZ_TYPE_ALLIANCE=>'联盟商户',
        self::BIZ_TYPE_AGENT=>'代理商',
        self::BIZ_TYPE_PAYMENT_HANDLING_FEE=>'支付渠道费',
        self::BIZ_TYPE_COMPANY=>'平台提成',
    ];

    public static $bizTypeShowArr = [
        self::BIZ_TYPE_CUSTOMER.'-'.self::LEVEL_ONE=>'一级分润',
        self::BIZ_TYPE_CUSTOMER.'-'.self::LEVEL_TWO=>'二级分润',
        self::BIZ_TYPE_POPULARIZER.'-'.self::LEVEL_ONE=>'一级分享收益',
        self::BIZ_TYPE_POPULARIZER.'-'.self::LEVEL_TWO=>'二级分享收益',
        self::BIZ_TYPE_DELIVERY.'-'.self::LEVEL_ONE=>'团长收益',
        self::BIZ_TYPE_ALLIANCE.'-'.self::LEVEL_ONE=>'联盟收益',
        self::BIZ_TYPE_AGENT.'-'.self::LEVEL_ONE=>'代理商收益',
        self::BIZ_TYPE_PAYMENT_HANDLING_FEE.'-'.self::LEVEL_ONE=>'支付渠道费',
        self::BIZ_TYPE_COMPANY.'-'.self::LEVEL_ONE=>'平台提成',
    ];

    const LEVEL_ONE = 1;
    const LEVEL_TWO = 2;
    public static $levelArr = [
        self::LEVEL_ONE=>'一级',
        self::LEVEL_TWO=>'二级',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_pre_distribute}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'order_time'], 'safe'],
            [['order_no', 'order_time', 'biz_id', 'biz_type'], 'required'],
            [['biz_id', 'biz_type', 'level', 'amount','order_amount','amount_ac'], 'integer'],
            [['order_no'], 'string', 'max' => 20],
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
            'order_no' => '订单编号',
            'order_time' => '时间',
            'order_amount'=>'订单金额',
            'biz_id' => '分润人ID',
            'biz_type' => '分润类型 1用户  2分享团长  3配送团长',
            'level' => '分销等级 1一级  2二级',
            'amount' => '分润金额',
            'amount_ac'=>'实际分润',
        ];
    }

}
