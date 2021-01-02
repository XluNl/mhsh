<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_customer_service}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $customer_id
 * @property string $order_no
 * @property integer $status
 * @property integer $type
 * @property integer $delivery_id
 * @property integer $company_id
 * @property integer $audit_level
 * @property string $remark
 * @property string $audit_remark
 * @property string $images
 */
class OrderCustomerService extends \yii\db\ActiveRecord
{

    const DEAL_PROCESS_UN_DONE =1;
    const DEAL_PROCESS_DONE =2;
    public static $dealProcessArr=[
        self::DEAL_PROCESS_UN_DONE=>'处理中',
        self::DEAL_PROCESS_DONE=>'已处理',
    ];

    const TYPE_REFUND_CHANGE =3;
    const TYPE_REFUND_MONEY_ONLY =4;
    const TYPE_REFUND_MONEY_AND_GOODS =5;
    const TYPE_REFUND_CLAIM = 6;

    public static $typeArr=[
        self::TYPE_REFUND_CHANGE=>'换货',
        self::TYPE_REFUND_MONEY_ONLY=>'仅退款',
        self::TYPE_REFUND_MONEY_AND_GOODS=>'退款退货',
        self::TYPE_REFUND_CLAIM=>'赔付',
    ];
    public static $typeCssArr=[
        self::TYPE_REFUND_CHANGE=>'label label-success',
        self::TYPE_REFUND_MONEY_ONLY=>'label label-danger',
        self::TYPE_REFUND_MONEY_AND_GOODS=>'label label-warning',
        self::TYPE_REFUND_CLAIM=>'label label-danger',
    ];

    const STATUS_UN_DEAL = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_DENY = 3;
    const STATUS_CANCEL = 4;

    public static $statusArr=[
        self::STATUS_UN_DEAL=>'审核中',
        self::STATUS_ACCEPT=>'审核通过',
        self::STATUS_DENY=>'审核拒绝',
        self::STATUS_CANCEL=>'主动撤回',
    ];

    public static $statusCssArr=[
        self::STATUS_UN_DEAL=>'label label-warning',
        self::STATUS_ACCEPT=>'label label-success',
        self::STATUS_DENY=>'label label-danger',
        self::STATUS_CANCEL=>'label label-info',
    ];

    /**团长级别*/
    const AUDIT_LEVEL_DELIVERY_OR_ALLIANCE = 1;
    /**代理商级别*/
    const AUDIT_LEVEL_AGENT = 2;

    public static $auditLevelArr=[
        self::AUDIT_LEVEL_DELIVERY_OR_ALLIANCE=>'团长/联盟点',
        self::AUDIT_LEVEL_AGENT=>'代理商',
    ];

    public static $auditLevelCssArr=[
        self::AUDIT_LEVEL_DELIVERY_OR_ALLIANCE=>'label label-success',
        self::AUDIT_LEVEL_AGENT=>'label label-info',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_customer_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['customer_id', 'status','delivery_id','company_id','audit_level'], 'integer'],
            [['order_no'], 'string', 'max' => 50],
            [['remark','audit_remark'], 'string', 'max' => 50],
            [['images'], 'string', 'max' => 4095],
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
            'customer_id' => '客户ID',
            'order_no' => '订单号',
            'status' => '状态',
            'delivery_id'=>'配送点',
            'company_id'=>'公司ID',
            'audit_level'=>'审核等级',
            'type'=>'售后类型',
            'remark'=>'备注',
            'audit_remark'=>'审核备注',
            'images'=>'申请图片',
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

    public function getDelivery(){
        return $this->hasOne(Delivery::className(),['id' => 'delivery_id']);
    }

    public function getOrder(){
        return $this->hasOne(Order::className(),['order_no' => 'order_no']);
    }

    public function getCustomerServiceGoods() {
        return $this->hasMany(OrderCustomerServiceGoods::className(), ['customer_service_id' => 'id'])
            ->with(['orderGoods']);
    }
    public function getLogs() {
        return $this->hasMany(OrderCustomerServiceLog::className(), ['customer_service_id' => 'id']);
    }
}
