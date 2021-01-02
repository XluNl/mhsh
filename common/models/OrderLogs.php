<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_log}}".
 *
 * @property integer $id
 * @property string $order_no
 * @property string $role
 * @property integer $user_id
 * @property string $name
 * @property string $action
 * @property string $remark
 * @property integer $status
 * @property string $created_at
 * @property integer $company_id
 */

class OrderLogs extends ActiveRecord {

    const ROLE_ADMIN = 1;
    const ROLE_SYSTEM = 2;
    const ROLE_DELIVERY = 3;
    const ROLE_CUSTOMER = 4;
    const ROLE_ALLIANCE = 5;
    const ROLE_COURIER = 6;
    const ROLE_STORAGE = 7;

	public static $role_list =[
        self::ROLE_ADMIN => '管理员',
        self::ROLE_SYSTEM => '平台',
        self::ROLE_DELIVERY => '配送团长',
        self::ROLE_CUSTOMER => '用户',
        self::ROLE_ALLIANCE => '异业联盟商户',
        self::ROLE_COURIER => '配送员',
        self::ROLE_STORAGE => '仓库人员',
    ];

	const ACTION_ORDER_CREATE = 2;
    const ACTION_ORDER_RECEIVE = 3;
    const ACTION_ORDER_UPLOAD_WEIGHT = 4;
    const ACTION_ORDER_UN_UPLOAD_WEIGHT = 5;
    const ACTION_ADMIN_ADD_NOTE  = 13;
    const ACTION_ORDER_DELIVERY_OUT = 6;
    const ACTION_ORDER_COMPLETE  = 12;
    const ACTION_ORDER_ALLIANCE_DELIVERY_OUT = 17;


    const ACTION_ASSIGN_COURIER= 1;
    const ACTION_MODIFY_ORDER_TYPE  = 7;
    const ACTION_CANCEL_ORDER  = 8;
    const ACTION_DELETE_ORDER  = 9;
    const ACTION_PAY_SUCCESS  = 10;
    const ACTION_PAY_REFUND  = 11;

    const ACTION_ADMIN_SET_PAY_SUCCESS  = 14;
    const ACTION_ADMIN_SET_PAY_REFUND = 15;
    const ACTION_ADMIN_SET_PAY_PARTY = 16;

    const ACTION_ORDER_ALLIANCE_RECEIVE = 18;
    const ACTION_ORDER_ALLIANCE_NO_STOCK = 19;

    const ACTION_DELIVERY_ORDER_DELIVERY_OUT = 20;

    const ACTION_HA_ORDER_DELIVERY_OUT = 21;

    const ACTION_COURIER_DELIVERY_RECEIVE = 22;

    const ACTION_STORAGE_DELIVERY_OUT = 23;

    const ACTION_GROUP_ACTIVE_TIME_OUT_CANCEL_ORDER  = 24;

    public static $action_list = array(
        self::ACTION_ORDER_CREATE => '用户创建订单',
        self::ACTION_ORDER_RECEIVE => '发货团长确认送达',
        self::ACTION_ORDER_UPLOAD_WEIGHT => '上传重量',
        self::ACTION_ORDER_UN_UPLOAD_WEIGHT => '取消上传重量',
        self::ACTION_MODIFY_ORDER_TYPE => '设置订单类型',
        self::ACTION_CANCEL_ORDER => '取消订单',
        self::ACTION_DELETE_ORDER => '删除订单',
        self::ACTION_PAY_SUCCESS => '确认支付',
        self::ACTION_PAY_REFUND => '撤销支付',
        self::ACTION_ORDER_COMPLETE => '订单完成',
        self::ACTION_ADMIN_ADD_NOTE => '管理员留言',
        self::ACTION_ADMIN_SET_PAY_SUCCESS => '管理员设置为未支付',
        self::ACTION_ADMIN_SET_PAY_REFUND => '管理员设置为已退款',
        self::ACTION_ADMIN_SET_PAY_PARTY => '管理员设置为部分支付',
        self::ACTION_ORDER_DELIVERY_OUT=>'平台发货',
        self::ACTION_ORDER_ALLIANCE_DELIVERY_OUT=>'异业联盟商家发货',
        self::ACTION_ORDER_ALLIANCE_RECEIVE=>'异业联盟商家确认送达',
        self::ACTION_ORDER_ALLIANCE_NO_STOCK=>'异业联盟商家确认无货',
        self::ACTION_DELIVERY_ORDER_DELIVERY_OUT=>'团长发货',
        self::ACTION_HA_ORDER_DELIVERY_OUT=>'联盟发货',
        self::ACTION_COURIER_DELIVERY_RECEIVE=>'配送员送达',
        self::ACTION_STORAGE_DELIVERY_OUT=>'仓库发货',
        self::ACTION_GROUP_ACTIVE_TIME_OUT_CANCEL_ORDER=>'拼团超时自动退款',
	);


	public static function tableName() {
		return "{{%order_log}}";
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no'], 'required'],
            [['user_id', 'status', 'company_id', 'action','role'], 'integer'],
            [['created_at'], 'safe'],
            [['order_no', 'name'], 'string', 'max' => 20],
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
            'order_no' => '订单号',
            'role' => '操作人类型',
            'user_id' => '操作人ID',
            'name' => '操作人姓名',
            'action' => '操作',
            'remark' => '备注',
            'status' => '操作状态',
            'created_at' => '创建时间',
            'company_id' => 'Company ID',
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