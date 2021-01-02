<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%group_room_wait_refund_order_item}}".
 *
 * @property int $id id
 * @property int $wait_refund_order_id 待退款单id
 * @property string $active_no 活动NO
 * @property string $room_no 拼团NO
 * @property string $order_no 订单号
 * @property int $customer_id 用户id
 * @property int $refund_type 退款方式 1余额  2微信
 * @property int $refund_amount 待退款金额
 * @property string|null $refund_no 退款单号
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $company_id 公司id
 */
class GroupRoomWaitRefundOrderItem extends \yii\db\ActiveRecord
{

    const REFUND_TYPE_BALANCE = 1;
    const REFUND_TYPE_WECHAT = 2;
    public static $refundTypeArr = [
        self::REFUND_TYPE_BALANCE=>'余额',
        self::REFUND_TYPE_WECHAT=>'微信',
    ];
    public static $refundTypeCssArr = [
        self::REFUND_TYPE_BALANCE=>'label label-primary',
        self::REFUND_TYPE_WECHAT=>'label label-success',
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%group_room_wait_refund_order_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active_no', 'room_no', 'order_no', 'customer_id','wait_refund_order_id','company_id'], 'required'],
            [['customer_id', 'refund_type', 'refund_amount','company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['active_no', 'room_no'], 'string', 'max' => 50],
            [['order_no', 'refund_no'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'wait_refund_order_id'=>'待退款单id',
            'active_no' => '活动NO',
            'room_no' => '拼团NO',
            'order_no' => '订单号',
            'customer_id' => '用户id',
            'refund_type' => '退款方式 1余额  2微信',
            'refund_amount' => '待退款金额',
            'refund_no' => '退款单号',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => '公司id',
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
