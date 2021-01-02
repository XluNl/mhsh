<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order_goods}}".
 *
 * @property integer $id
 * @property string $order_no
 * @property integer $goods_id
 * @property string $goods_name
 * @property string $goods_img
 * @property string $goods_describe
 * @property integer $sort_1
 * @property integer $sort_2
 * @property integer $sku_id
 * @property string $sku_name
 * @property string $sku_img
 * @property integer $sku_price
 * @property string $sku_unit
 * @property double $sku_unit_factor
 * @property integer $sku_standard
 * @property integer $status
 * @property integer $delivery_status
 * @property double $num
 * @property double $num_ac
 * @property integer $amount
 * @property integer $amount_ac
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $delivery_id
 * @property integer $purchase_price
 * @property integer $reference_price
 * @property integer $discount
 * @property string $remark
 * @property integer $share_rate_1
 * @property integer $share_rate_2
 * @property integer $delivery_rate
 * @property integer $goods_type
 * @property integer $goods_owner
 * @property integer $goods_owner_id
 * @property integer $schedule_id
 * @property string $schedule_name
 * @property string $validity_start
 * @property string $validity_end
 * @property string $expect_arrive_time
 * @property integer $customer_service_status
 * @property integer $one_level_rate
 * @property integer $two_level_rate
 * @property integer $agent_rate
 * @property integer $company_rate
 */

class OrderGoods extends ActiveRecord {

    public $refund_amount;

    const DELIVERY_STATUS_PREPARE = -1;
    const DELIVERY_STATUS_DELIVERY = 0;
    const DELIVERY_STATUS_SELF_DELIVERY = 1;
    const DELIVERY_STATUS_SUCCESS = 2;
    const DELIVERY_STATUS_REFUND_CHANGE =3;
    const DELIVERY_STATUS_REFUND_MONEY_ONLY =4;
    const DELIVERY_STATUS_REFUND_MONEY_AND_GOODS =5;
    const DELIVERY_STATUS_CLAIM = 6;

    /**
     * 不支持的确认送达状态
     * @var array
     */
    public static $unReceiveDeliveryStatus=[
        self::DELIVERY_STATUS_PREPARE,
        self::DELIVERY_STATUS_DELIVERY,
        self::DELIVERY_STATUS_SELF_DELIVERY,
        self::DELIVERY_STATUS_REFUND_CHANGE,
    ];

    /**
     * 可以上传重量的状态
     * @var int[]
     */
    public static $canUploadWeightStatus=[
        self::DELIVERY_STATUS_DELIVERY,
        self::DELIVERY_STATUS_SELF_DELIVERY,
        self::DELIVERY_STATUS_SUCCESS,
        self::DELIVERY_STATUS_REFUND_CHANGE,
    ];

    /**
     * 需要自动上传重量的状态
     * @var int[]
     */
    public static $needAutoUploadWeightStatus=[
        self::DELIVERY_STATUS_DELIVERY,
        self::DELIVERY_STATUS_SELF_DELIVERY,
        self::DELIVERY_STATUS_REFUND_CHANGE,
    ];

    /**
     * 可以确认收货的状态
     * @var int[]
     */
    public static $canReceiveOrderStatus=[
        self::DELIVERY_STATUS_DELIVERY,
        self::DELIVERY_STATUS_SELF_DELIVERY,
        self::DELIVERY_STATUS_SUCCESS,
        self::DELIVERY_STATUS_REFUND_CHANGE,
        self::DELIVERY_STATUS_REFUND_MONEY_ONLY,
        self::DELIVERY_STATUS_REFUND_MONEY_AND_GOODS,
        self::DELIVERY_STATUS_CLAIM,
    ];

    /**
     * 可以发起售后
     * @var int[]
     */
    public static $canCustomerServiceStatusArr=[
        OrderGoods::DELIVERY_STATUS_PREPARE,
        OrderGoods::DELIVERY_STATUS_DELIVERY,
        OrderGoods::DELIVERY_STATUS_SELF_DELIVERY,
        OrderGoods::DELIVERY_STATUS_SUCCESS
    ];


    public static $deliveryStatusArr=[
        self::DELIVERY_STATUS_PREPARE=>'备货中',
        self::DELIVERY_STATUS_DELIVERY=>'配送中',
        self::DELIVERY_STATUS_SELF_DELIVERY=>'待提货',
        self::DELIVERY_STATUS_SUCCESS=>'已提货',
        self::DELIVERY_STATUS_REFUND_CHANGE=>'调换货',
        self::DELIVERY_STATUS_REFUND_MONEY_ONLY=>'仅退款',
        self::DELIVERY_STATUS_REFUND_MONEY_AND_GOODS=>'退款退货',
        self::DELIVERY_STATUS_CLAIM=>'余额赔付',
    ];

    public static $deliveryStatusCssArr=[
        self::DELIVERY_STATUS_PREPARE=>'label label-info',
        self::DELIVERY_STATUS_DELIVERY=>'label label-default',
        self::DELIVERY_STATUS_SELF_DELIVERY=>'label label-primary',
        self::DELIVERY_STATUS_SUCCESS=>'label label-warning',
        self::DELIVERY_STATUS_REFUND_CHANGE=>'label label-danger',
        self::DELIVERY_STATUS_REFUND_MONEY_ONLY=>'label label-success',
        self::DELIVERY_STATUS_REFUND_MONEY_AND_GOODS=>'label label-default',
        self::DELIVERY_STATUS_CLAIM=>'label label-primary',
    ];

    const CUSTOMER_SERVICE_STATUS_FALSE = 0;
    const CUSTOMER_SERVICE_STATUS_TRUE =1;
    public static $customerServiceStatusArr=[
        self::CUSTOMER_SERVICE_STATUS_FALSE=>'无售后',
        self::CUSTOMER_SERVICE_STATUS_TRUE=>'有售后',
    ];
    public static $customerServiceStatusCssArr=[
        self::CUSTOMER_SERVICE_STATUS_FALSE=>'label label-success',
        self::CUSTOMER_SERVICE_STATUS_TRUE=>'label label-danger',
    ];

    public static function getDeliveryStatusTextById($status){
        if (key_exists($status,self::$deliveryStatusArr)){
            return self::$deliveryStatusArr[$status];
        }
        return "未知";
    }

    public static function tableName() {
		return "{{%order_goods}}";
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => '订单号',
            'goods_id' => '商品ID',
            'goods_name' => '商品名称',
            'goods_img'=>'商品照片',
            'goods_describe'=>'商品描述',
            'sort_1' => '一级分类',
            'sort_2' => '二级分类',
            'sku_id' => '商品属性ID',
            'sku_name' => '商品名称',
            'sku_img' => '商品图片',
            'sku_price' => '商品金额',
            'sku_unit' => '商品单位',
            'sku_unit_factor' => '因子',
            'sku_standard' => '是否标准品',
            'status' => '状态值',
            'delivery_status'=>'配送状态',
            'num' => '商品数量',
            'num_ac' => '实际商品数量',
            'amount' => '金额',
            'amount_ac' => '实际金额',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'delivery_id' => '代收点ID',
            'purchase_price'=>'采购价',
            'reference_price'=>'划线价',
            'discount'=>'优惠折扣',
            'remark' => '备注',
            'share_rate_1'=>'一级分享比例',
            'share_rate_2'=>'二级分享比例',
            'delivery_rate'=>'配送比例',
            'goods_owner' => '商品归属',
            'goods_owner_id'=>'商品归属ID',
            'goods_type'=>'商品类别',
            'schedule_id'=>'排期ID',
            'schedule_name'=>'排期名称',
            'validity_start' => '有效期起始时间',
            'validity_end' => '有效期截止时间',
            'expect_arrive_time'=>'预计送达时间',
            'customer_service_status'=>'售后状态',
            'one_level_rate'=>'用户一级分销比例',
            'two_level_rate'=>'用户二级分销比例',
            'agent_rate'=>'代理商分销比例',
            'company_rate'=>'平台提成比例',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no', 'goods_id', 'goods_name', 'sku_id', 'sku_name', 'sku_price', 'num', 'amount'], 'required'],
            [['goods_id', 'sku_id','schedule_id', 'sku_price', 'sku_standard',
                'status', 'amount', 'amount_ac', 'company_id',
                'delivery_id','delivery_status','purchase_price',
                'reference_price','discount','sort_1','sort_2',
                'goods_type','goods_owner','goods_owner_id','customer_service_status'], 'integer'],
            [['sku_unit_factor', 'num', 'num_ac'], 'number'],
            [['share_rate_1', 'share_rate_2', 'delivery_rate','one_level_rate','two_level_rate','agent_rate'], 'number'],
            [['created_at', 'updated_at','validity_start','validity_end','expect_arrive_time'], 'safe'],
            [['order_no'], 'string', 'max' => 20],
            [['goods_name','goods_img','goods_img','sku_name', 'sku_img','remark','schedule_name'], 'string', 'max' => 255],
            [['sku_unit'], 'string', 'max' => 10],
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