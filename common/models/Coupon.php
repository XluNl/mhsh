<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "{{%coupon}}".
 *
 * @property string $id
 * @property string $coupon_no
 * @property string $name
 * @property integer $startup
 * @property integer $discount
 * @property integer $type
 * @property string $start_time
 * @property string $end_time
 * @property integer $status
 * @property string $order_no
 * @property string $use_time
 * @property string $batch
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 * @property integer $customer_id
 * @property integer $company_id
 * @property integer $limit_type
 * @property string $limit_type_params
 * @property integer $restore
 * @property integer $draw_operator_id
 * @property string $draw_operator_name
 * @property integer $draw_operator_type
 * @property integer $owner_type
 * @property integer $owner_id
 * @property integer $is_remind
 * @property integer $coupon_type
 */
class Coupon extends ActiveRecord
{

    public $customer_name;
    public $customer_phone;

    const TYPE_CASH_BACK = 1;
    const TYPE_DISCOUNT = 2;


    const SORT_ALL = -1;

    public static  $typeArr=[
        self::TYPE_CASH_BACK=>'满减',
        self::TYPE_DISCOUNT=>'折扣',
    ];
    public static  $typeCssArr=[
        self::TYPE_CASH_BACK=>'label label-success',
        self::TYPE_DISCOUNT=>'label label-danger',
    ];
    public static  $typeDisplayArr=[
        self::TYPE_CASH_BACK=>'满减',
    ];


    const STATUS_ACTIVE = 1;
    const STATUS_USED = 2;
    const STATUS_DISCARD = 3;

    public static  $statusArr=[
        self::STATUS_ACTIVE=>'可用',
        self::STATUS_USED=>'已用',
        self::STATUS_DISCARD=>'作废',
    ];
    public static  $statusCssArr=[
        self::STATUS_ACTIVE=>'label label-success',
        self::STATUS_USED=>'label label-warning',
        self::STATUS_DISCARD=>'label label-danger',
    ];

    const LIMIT_TYPE_ALL = 1;
    const LIMIT_TYPE_OWNER = 2;
    const LIMIT_TYPE_SORT = 3;
    const LIMIT_TYPE_GOODS_SKU = 4;

    public static $limitTypeArr=[
        self::LIMIT_TYPE_ALL=>'全品类',
        self::LIMIT_TYPE_OWNER=>'限大品类',
        self::LIMIT_TYPE_SORT=>'限品类',
        self::LIMIT_TYPE_GOODS_SKU=>'限定商品',
    ];
    public static $limitTypeCssArr=[
        self::LIMIT_TYPE_ALL=>'label label-success',
        self::LIMIT_TYPE_OWNER=>'label label-info',
        self::LIMIT_TYPE_SORT=>'label label-warning',
        self::LIMIT_TYPE_GOODS_SKU=>'label label-danger',
    ];



    const RESTORE_TRUE = 1;
    const RESTORE_FALSE = 2;

    public static  $restoreArr=[
        self::RESTORE_TRUE=>'可恢复',
        self::RESTORE_FALSE=>'不可恢复',
    ];
    public static  $restoreCssArr=[
        self::RESTORE_TRUE=>'label label-success',
        self::RESTORE_FALSE=>'label label-danger',
    ];

    const IS_REMIND_FALSE = 0;
    const IS_REMIND_TRUE = 1;

    public static  $isRemindArr=[
        self::IS_REMIND_FALSE=>'未提醒',
        self::IS_REMIND_TRUE=>'已提醒',
    ];
    public static  $isRemindCssArr=[
        self::IS_REMIND_FALSE=>'label label-success',
        self::IS_REMIND_TRUE=>'label label-danger',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%coupon}}';
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['startup', 'discount', 'end_time'], 'required'],
            [['startup', 'discount', 'type', 'status', 'limit_type', 'customer_id', 'company_id','restore','draw_operator_id','draw_operator_type','owner_type','owner_id','is_remind','coupon_type'], 'integer'],
            [['start_time', 'end_time', 'use_time', 'created_at','limit_type_params'], 'safe'],
            [['coupon_no', 'order_no', 'batch'], 'string', 'max' => 20],
            [['name', 'remark','draw_operator_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coupon_no' => '优惠券编号',
            'name' => '优惠券展示名称',
            'startup' => '满金额',
            'discount' => '减金额',
            'type' => '优惠券类型',
            'start_time' => '有效期开始时间',
            'end_time' => '有效期结束时间',
            'status' => '状态',
            'order_no' => '使用订单',
            'use_time' => '使用时间',
            'batch' => '批次',
            'remark' => '备注',
            'created_at' => '领取时间',
            'updated_at' => '更新时间',
            'customer_id' => '优惠券归属人',
            'customer_name' => '归属人名称',
            'customer_phone' => '归属人手机号',
            'company_id' => 'Company ID',
            'limit_type' => '优惠券限制类型',
            'limit_type_params' => '优惠券限制参数',
            'restore'=>'是否可恢复',
            'draw_operator_id'=>'领取人id',
            'draw_operator_name'=>'领取人姓名',
            'draw_operator_type'=>'领取人类型',
            'owner_type'=>'归属类型',
            'owner_id'=>'归属id',
            'is_remind'=>'是否已提醒',
            'coupon_type'=>'优惠券类型',
        ];
    }


    public function generate_no() {
        $yCode = array('CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ');
        $orderSn = $yCode[intval(date('Y')) - 2015] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%04d', rand(0, 9999));
        return $orderSn;
    }

    public static function generateDesc($type,$startup,$discount, $sortId= self::SORT_ALL){
        if ($sortId==self::SORT_ALL){
            $remark = '全品类';
        }
        else{
            $sortModel = ( new Query())->from(GoodsSort::tableName())->where(['id'=>$sortId])->one();
            if ($sortModel!=null){
                $remark ="{$sortModel['sort_name']}品类";
            }
            else{
                return "unknown";
            }
        }
        if ($type==self::TYPE_CASH_BACK){
            return $remark."满".Common::showAmount($startup).'元减'.Common::showAmount($discount).'元';
        }
        else if ($type==self::TYPE_DISCOUNT){
            return $remark."满".Common::showAmount($startup).'元打'.Common::showAmount($discount).'折';
        }
        else{
            return "unknown";
        }
    }

    public function getBatch_info(){
        return $this->hasOne(CouponBatch::className(), ['id' => 'batch']);
    }

}
