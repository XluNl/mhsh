<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%group_room_wait_refund_order}}".
 *
 * @property int $id id
 * @property string $active_no 活动NO
 * @property string $room_no 拼团NO
 * @property string $order_no 订单号
 * @property int $customer_id 用户id
 * @property int $order_amount 订单金额
 * @property int $refund_amount 待退款金额
 * @property int $status 0待处理 1成功 2失败
 * @property int $refund_action 0不需要处理  1退款    2退团差价
 * @property string $created_at
 * @property string $updated_at 更新时间
 * @property int $company_id 公司id
 */
class GroupRoomWaitRefundOrder extends \yii\db\ActiveRecord
{   

    const REFUND_STATUS_WAIT = 0;
    const REFUND_STATUS_SUCCESS = 1;
    const REFUND_STATUS_FAILED = 2;
    public static $refundStatusArr = [
        self::REFUND_STATUS_WAIT =>'待处理',
        self::REFUND_STATUS_SUCCESS =>'处理成功',
        self::REFUND_STATUS_FAILED =>'处理失败',
    ];

    public static $refundStatusCssArr = [
        self::REFUND_STATUS_WAIT =>'label label-warning',
        self::REFUND_STATUS_SUCCESS =>'label label-success',
        self::REFUND_STATUS_FAILED =>'label label-danger',
    ];

    const REFUND_ACTION_NOTHING = 0;
    const REFUND_ACTION_CANCEL = 1;
    const REFUND_ACTION_PART = 2;
    public static $refundActionArr = [
        self::REFUND_ACTION_NOTHING =>'不处理',
        self::REFUND_ACTION_CANCEL =>'取消订单',
        self::REFUND_ACTION_PART =>'退团差价',
    ];
    public static $refundActionCssArr = [
        self::REFUND_ACTION_NOTHING =>'label label-info',
        self::REFUND_ACTION_CANCEL =>'label label-warning',
        self::REFUND_ACTION_PART =>'label label-primary',
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%group_room_wait_refund_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active_no', 'room_no', 'order_no', 'customer_id', 'order_amount','company_id'], 'required'],
            [['customer_id', 'order_amount', 'refund_amount', 'status', 'refund_action','company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['active_no', 'room_no'], 'string', 'max' => 50],
            [['order_no'], 'string', 'max' => 64],
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

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'active_no' => '活动NO',
            'room_no' => '拼团NO',
            'order_no' => '订单号',
            'customer_id' => '用户id',
            'order_amount' => '订单金额',
            'refund_amount' => '待退款金额',
            'status' => '处理状态',
            'refund_action' => '退款操作',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id'=>'公司ID',
        ];
    }

    public function getOrder(){
        return $this->hasOne(Order::className(),['order_no'=>'order_no']);
    }
}
