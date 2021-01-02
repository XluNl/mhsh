<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%distribute_balance_item}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $user_id
 * @property integer $biz_type
 * @property integer $biz_id
 * @property integer $type_id
 * @property integer $distribute_balance_id
 * @property string $order_no
 * @property integer $order_amount
 * @property integer $amount
 * @property integer $status
 * @property string $distribute_detail
 * @property integer $in_out
 * @property integer $type
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $remain_amount
 * @property integer $action
 * @property string $remark
 */
class DistributeBalanceItem extends \yii\db\ActiveRecord
{
    const TYPE_ORDER_DISTRIBUTE = 1;
    const TYPE_WITHDRAW = 2;
    const TYPE_DRAW_BONUS = 3;
    const TYPE_CUSTOMER_INVITATION_ACTIVITY_BONUS = 4;
    const TYPE_CLAIM = 5;
    const TYPE_DELIVER_FEE = 6;
    const TYPE_DELIVERY_COMMODITY_WARRANTY = 7;

    public static $typeArr=[
        self::TYPE_ORDER_DISTRIBUTE=>'订单分润',
        self::TYPE_WITHDRAW=>'提现',
        self::TYPE_DRAW_BONUS=>'奖励金发放',
        self::TYPE_CUSTOMER_INVITATION_ACTIVITY_BONUS=>'邀请拉新活动发放',
        self::TYPE_CLAIM=>'订单赔付',
        self::TYPE_DELIVER_FEE=>'配送费代扣',
        self::TYPE_DELIVERY_COMMODITY_WARRANTY=>'商品质保金充值',
    ];
    public static $typeCssArr=[
        self::TYPE_ORDER_DISTRIBUTE=>'label label-info',
        self::TYPE_WITHDRAW=>'label label-success',
        self::TYPE_DRAW_BONUS=>'label label-primary',
        self::TYPE_CUSTOMER_INVITATION_ACTIVITY_BONUS=>'label label-warning',
        self::TYPE_CLAIM=>'label label-danger',
        self::TYPE_DELIVER_FEE=>'label label-default',
        self::TYPE_DELIVERY_COMMODITY_WARRANTY=>'label label-primary',
    ];

    public static $claimTypeArr=[
        self::TYPE_CLAIM=>'订单赔付',
        self::TYPE_DELIVER_FEE=>'配送费代扣',
    ];

    const IN_OUT_IN  = 1;
    const IN_OUT_OUT  = 2;

    public static $inOutArr=[
        self::IN_OUT_IN=>'收入',
        self::IN_OUT_OUT=>'支出',
    ];
    public static $inOutCssArr=[
        self::IN_OUT_IN=>'label label-success',
        self::IN_OUT_OUT=>'label label-danger',
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
        return '{{%distribute_balance_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'biz_type', 'biz_id', 'type_id','distribute_balance_id', 'order_amount', 'amount', 'status', 'in_out', 'type', 'operator_id','user_id','remain_amount','action'], 'integer'],
            [['biz_type', 'biz_id', 'type_id','distribute_balance_id', 'amount', 'in_out', 'type','user_id','action'], 'required'],
            [['order_no'], 'string', 'max' => 50],
            [['distribute_detail'], 'string', 'max' => 10240],
            [['operator_name'], 'string', 'max' => 100],
            [['remark'], 'string', 'max' => 255],
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
            'updated_at' => '修改时间',
            'company_id' => 'Company ID',
            'user_id'=> '用户ID',
            'biz_type' => '账户类型',
            'biz_id' => '业务ID',
            'type' => '日志类型',
            'distribute_balance_id'=>'分润账户id',
            'type_id' => '日志业务对应的id',
            'order_no' => '订单号',
            'order_amount' => '订单金额',
            'amount' => '分润金额',
            'status' => '状态',
            'distribute_detail' => '分润明细',
            'in_out' => '出入账类型',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人名称',
            'remain_amount'=>'操作后金额',
            'action'=>'状态',
            'remark'=>'备注',
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

    public function getOrder() {
        return $this->hasOne(Order::className(), ['order_no' => "order_no"]);
    }

}
