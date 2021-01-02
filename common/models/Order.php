<?php

namespace common\models;
use common\services\OrderService;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property integer $id
 * @property string $order_no
 * @property integer $order_status
 * @property integer $goods_num
 * @property double $goods_num_ac
 * @property integer $freight_amount
 * @property integer $discount_amount
 * @property string $discount_details
 * @property integer $need_amount
 * @property integer $need_amount_ac
 * @property integer $real_amount
 * @property integer $real_amount_ac
 * @property integer $order_type
 * @property integer $order_owner
 * @property integer $order_owner_id
 * @property string $pay_id
 * @property string $prepay_id
 * @property string $pay_name
 * @property integer $pay_type
 * @property string $pay_result
 * @property integer $pay_status
 * @property string $pay_time
 * @property integer $pay_amount
 * @property integer $balance_pay_amount
 * @property integer $three_pay_amount
 * @property string $send_time
 * @property integer $customer_id
 * @property integer $customer_point
 * @property string $accept_nickname
 * @property string $accept_name
 * @property string $accept_mobile
 * @property integer $accept_province_id
 * @property integer $accept_city_id
 * @property integer $accept_county_id
 * @property string $accept_community
 * @property string $accept_address
 * @property double $accept_lat
 * @property double $accept_lng
 * @property integer $accept_period
 * @property integer $accept_delivery_type
 * @property string $accept_time
 * @property string $completion_time
 * @property integer $share_rate_id_1
 * @property integer $share_rate_id_2
 * @property integer $delivery_id
 * @property string $delivery_nickname
 * @property string $delivery_name
 * @property string $delivery_phone
 * @property string $delivery_code
 * @property string $created_at
 * @property string $updated_at
 * @property string $order_note
 * @property string $admin_note
 * @property string $cancel_remark
 * @property integer $company_id
 * @property integer $evaluate
 * @property integer $customer_service_status
 * @property integer $one_level_rate_id
 * @property integer $two_level_rate_id
 */
class Order extends ActiveRecord {

    public static function getOrderTypeArr(){
        return GoodsConstantEnum::$typeArr;
    }
    public static function getOrderOwnerArr(){
        return GoodsConstantEnum::$ownerArr;
    }
    public static function getDeliveryTypeArr(){
        return GoodsConstantEnum::$deliveryTypeArr;
    }

    const  EVALUATE_FALSE = 0;
	const  EVALUATE_TRUE = 1;
    public static $evaluateArr = [
        self::EVALUATE_FALSE => '未评价',
        self::EVALUATE_TRUE => '已评价',
    ];

    const CUSTOMER_SERVICE_STATUS_TRUE=1;
    const CUSTOMER_SERVICE_STATUS_FALSE=0;

    public static $customerServiceStatusArr = [
        self::CUSTOMER_SERVICE_STATUS_TRUE => '有售后',
        self::CUSTOMER_SERVICE_STATUS_FALSE => '无售后',
    ];

	public static $time_list = [
        0 => "0:00-1:00",
        1 => "1:00-2:00",
		2 => "2:00-3:00",
		3 => "3:00-4:00",
		4 => "4:00-5:00",
		5 => "5:00-6:00",
		6 => "6:00-7:00",
		7 => "7:00-8:00",
        8 => "8:00-9:00",
        9 => "9:00-10:00",
        10 => "10:00-11:00",
        11 => "11:00-12:00",
        12 => "12:00-13:00",
        13 => "13:00-14:00",
        14 => "14:00-15:00",
        15 => "15:00-16:00",
        16 => "16:00-17:00",
        17 => "17:00-18:00",
        18 => "18:00-19:00",
        19 => "19:00-20:00",
        20 => "20:00-21:00",
        21 => "21:00-22:00",
        22 => "22:00-23:00",
        23 => "23:00-24:00",
	];

	const ORDER_STATUS_UN_PAY = 0;
    const ORDER_STATUS_DELIVERY = 2;
    const ORDER_STATUS_PREPARE = 1;
    const ORDER_STATUS_SELF_DELIVERY = 3;
    const ORDER_STATUS_RECEIVE = 4;
    const ORDER_STATUS_COMPLETE = 5;
    const ORDER_STATUS_CANCELING = 6;
    const ORDER_STATUS_CANCELED = 7;
    const ORDER_STATUS_CHECKING = 8;

    public static $logicOrderStatusDisplayOrder=[
        self::ORDER_STATUS_UN_PAY,
        self::ORDER_STATUS_CHECKING,
        self::ORDER_STATUS_PREPARE,
        self::ORDER_STATUS_DELIVERY,
        self::ORDER_STATUS_SELF_DELIVERY,
        self::ORDER_STATUS_RECEIVE,
        self::ORDER_STATUS_COMPLETE,
        self::ORDER_STATUS_CANCELING,
        self::ORDER_STATUS_CANCELED,
    ];

	public static $order_status_list = [
		self::ORDER_STATUS_UN_PAY => '待付款',
        self::ORDER_STATUS_PREPARE => '待发货',
        self::ORDER_STATUS_DELIVERY => '配送中',
        self::ORDER_STATUS_SELF_DELIVERY => '待提货',
        self::ORDER_STATUS_RECEIVE => '已送达',
        self::ORDER_STATUS_COMPLETE => '已完成',
        self::ORDER_STATUS_CANCELING => '取消中',
        self::ORDER_STATUS_CANCELED => '已取消',
        self::ORDER_STATUS_CHECKING => '待成团',
	];
    public static $order_status_list_for_alliance = [
        self::ORDER_STATUS_UN_PAY => '待付款',
        self::ORDER_STATUS_PREPARE => '待发货',
        self::ORDER_STATUS_DELIVERY => '配送中',
        self::ORDER_STATUS_SELF_DELIVERY => '配送中',
        self::ORDER_STATUS_RECEIVE => '已送达',
        self::ORDER_STATUS_COMPLETE => '已完成',
        self::ORDER_STATUS_CANCELING => '取消中',
        self::ORDER_STATUS_CANCELED => '已取消',
        self::ORDER_STATUS_CHECKING => '待成团',
    ];
    public static $order_status_list_css = [
        self::ORDER_STATUS_UN_PAY => 'btn btn-default btn-xs',
        self::ORDER_STATUS_PREPARE => 'btn btn-info btn-xs',
        self::ORDER_STATUS_DELIVERY => 'btn btn-primary btn-xs',
        self::ORDER_STATUS_SELF_DELIVERY => 'btn btn-primary btn-xs',
        self::ORDER_STATUS_RECEIVE => 'btn btn-warning btn-xs',
        self::ORDER_STATUS_COMPLETE => 'btn btn-success btn-xs',
        self::ORDER_STATUS_CANCELING => 'btn btn-danger btn-xs',
        self::ORDER_STATUS_CANCELED => 'btn btn-danger btn-xs',
        self::ORDER_STATUS_CHECKING => 'btn btn-warning btn-xs',
    ];


    /**
     * @var int[]
     */
    public static $canCustomerServiceStatusArr=[
        Order::ORDER_STATUS_DELIVERY,
        Order::ORDER_STATUS_SELF_DELIVERY,
        Order::ORDER_STATUS_RECEIVE
    ];

    /**
     * 可以上传重量的状态
     * @var int[]
     */
    public static $canUploadWeightStatusArr=[
        self::ORDER_STATUS_DELIVERY,
        self::ORDER_STATUS_SELF_DELIVERY,
        self::ORDER_STATUS_RECEIVE,
    ];

    /**
     * 可以确认收货
     * @var int[]
     */
    public static $canReceiveOrderStatusArr=[
        self::ORDER_STATUS_DELIVERY,
        self::ORDER_STATUS_SELF_DELIVERY,
    ];


    /**
     * 有效订单状态限制
     * @var array
     */
    public static $activeStatusArr=[
        self::ORDER_STATUS_PREPARE,
        self::ORDER_STATUS_DELIVERY,
        self::ORDER_STATUS_SELF_DELIVERY,
        self::ORDER_STATUS_RECEIVE,
        self::ORDER_STATUS_COMPLETE,
    ];

    /**
     * 下载订单状态限制
     * @var array
     */
	public static $downloadStatusArr=[
        self::ORDER_STATUS_PREPARE,
        self::ORDER_STATUS_DELIVERY,
        self::ORDER_STATUS_SELF_DELIVERY,
        self::ORDER_STATUS_RECEIVE,
        self::ORDER_STATUS_COMPLETE,
    ];


    public static $allowCancelStatusArr=[
        self::ORDER_STATUS_UN_PAY,
        //self::ORDER_STATUS_CHECKING,
        self::ORDER_STATUS_PREPARE,
    ];

    const PAY_STATUS_UN_PAY = 0;
    const PAY_STATUS_PAYED_ALL = 1;
    const PAY_STATUS_REFUND = 2;
    const PAY_STATUS_REFUND_PART = 3;
	public static $pay_status_list = [
		0 => '未支付',
		1 => '全部支付',
		2 => '全部已退款',
	    3 => '部分退款',
	];

	const DISCOUNT_TYPE_COUPON = 'coupon';

    public static $discount_type_list = [
        self::DISCOUNT_TYPE_COUPON => '优惠券',
    ];

	public static function tableName() {
		return "{{%order}}";
	}

	public function rules() {
		return [
            [['order_no', 'order_status', 'order_type',  'customer_id', 'accept_nickname', 'accept_name', 'accept_mobile', 'accept_province_id', 'accept_city_id', 'accept_county_id', 'accept_address'], 'required'],
            [['order_status', 'goods_num', 'freight_amount',
                'discount_amount', 'need_amount', 'need_amount_ac',
                'real_amount', 'real_amount_ac', 'order_type',
                'pay_status', 'pay_amount', 'customer_id',
                'customer_point', 'accept_province_id', 'accept_city_id',
                'accept_county_id', 'accept_period', 'delivery_id',
                'company_id',
                'balance_pay_amount','evaluate','accept_delivery_type',
                'share_rate_id_1','share_rate_id_2',
                'pay_type', 'order_owner','order_owner_id','one_level_rate_id','two_level_rate_id'], 'integer'],
            [['goods_num_ac'], 'number'],
            [['pay_time', 'send_time', 'accept_time', 'completion_time', 'created_at', 'updated_at'], 'safe'],
            [['admin_note'], 'string'],
            [['order_no', 'pay_name', 'accept_nickname', 'accept_name', 'accept_mobile', 'delivery_nickname', 'delivery_name', 'delivery_phone'], 'string', 'max' => 50],
            [['pay_id', 'accept_address','accept_community','cancel_remark','prepay_id'], 'string', 'max' => 255],
            [['pay_result'], 'string', 'max' => 50],
            [['discount_details'], 'string', 'max' => 1023],
            [['order_no'], 'unique'],
            [['delivery_code'], 'string', 'max' => 10],
            [['delivery_code'], 'unique'],
            [['accept_lat', 'accept_lng'], 'number'],
            ['customer_service_status','default','value' => 0]
		];
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => '订单编号',
            'order_status' => '订单状态',
            'goods_num' => '商品总数',
            'goods_num_ac' => '实际商品总数',
            'freight_amount' => '运费',
            'discount_amount' => '总优惠金额',
            'discount_details' => '优惠详情',
            'need_amount' => '应付商品金额',
            'need_amount_ac' => '实际应付商品金额',
            'real_amount' => '最终商品总额',
            'real_amount_ac' => '实际最终商品总额',
            'order_type' => '订单类型',
            'order_owner' => '订单归属',
            'order_owner_id' => '异业联盟点',
            'pay_id' => '支付方式ID',
            'prepay_id'=>'预支付id',
            'pay_name' => '支付方式',
            'pay_type' => '支付方式类型',
            'pay_result' => '支付结果',
            'pay_status' => '支付状态',
            'pay_time' => '付款时间',
            'pay_amount' => '已经支付了的金额',
            'balance_pay_amount' => '余额支付金额',
            'send_time' => '送货时间',
            'customer_id' => '用户ID',
            'customer_point' => '用户积分',
            'accept_nickname' => '用户昵称',
            'accept_name' => '收货人姓名',
            'accept_mobile' => '收货人电话',
            'accept_province_id' => '收货人省份',
            'accept_city_id' => '收货人城市',
            'accept_county_id' => '收货人地区',
            'accept_community'=>'收货人小区',
            'accept_address' => '收货地址',
            'accept_lat' => '收货地址纬度',
            'accept_lng' => '收货地址经度',
            'accept_period' => '用户要求送货时间段',
            'accept_time' => '用户收货时间',
            'accept_delivery_type'=>'配送方式',
            'completion_time' => '订单完成时间',
            'share_rate_id_1'=>'一级分享者ID',
            'share_rate_id_2'=>'二级分享者ID',
            'delivery_id' => '配送点',
            'delivery_nickname' => '配送点名称',
            'delivery_name' => '配送点联系人',
            'delivery_phone' => '配送点联系电话',
            'delivery_code' => '提货码',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'order_note' => '订单留言',
            'admin_note' => '管理员留言',
            'cancel_remark'=> '取消备注',
            'purchase_price'=>'采购价',
            'reference_price'=>'划线价',
            'company_id' => 'Company ID',
            'evaluate'=>'评价状态',
            'customer_service_status'=>'有无售后',
            'one_level_rate_id'=>'用户一级分销id',
            'two_level_rate_id'=>'用户二级分销id',
        ];
    }

	public function getGoods() {
		return $this->hasMany(OrderGoods::className(), ['order_no' => "order_no"])
			->where(['status' => CommonStatus::STATUS_ACTIVE]);
	}

	public function getLogs() {
        $condition = ["status" => CommonStatus::STATUS_ACTIVE];
        $query = $this->hasMany(OrderLogs::className(), ['order_no' => 'order_no'])
            ->where($condition)
            ->orderBy("created_at desc");
		return $query;
	}

    public function getEvaluate() {
        return $this->hasOne(Evaluate::className(), ['order_no' => 'order_no']);
    }

    public function getDelivery() {
        return $this->hasOne(Delivery::className(), ['id' => 'delivery_id']);
    }

    public function getAlliance() {
        return $this->hasOne(Alliance::className(), ['id' => 'order_owner_id']);
    }

    public function getCustomer() {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }


    public function getPreDistributes() {
        return $this->hasMany(OrderPreDistribute::className(), ['order_no' => "order_no"])->orderBy('biz_type,level');
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



	public function generateOrderNo() {
	    $yCode = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T'];
	    $orderSn = $yCode[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
	    return $orderSn;
	}

    public function generateDeliveryCode($customerId) {
        //2019/5/2 00:0:0 开始计时
        //系统最后时间2051/1/8 1:46:40
        $time = time()-1556726400;
        $charArr="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charLen = strlen($charArr);
        $code = "";
        $l = $customerId*1000000000+$time;
        for ($i=0;$i<9;$i++){
            $n = $l%$charLen;
            $code = $charArr[$n].$code;
            $l = ($l-$n)/$charLen;
        }
        return $code;
    }



	//计算real_amount;
	public function calcRealAmount(){
	    return OrderService::calcRealAmount($this->need_amount,$this->freight_amount,$this->discount_amount);
	}


}