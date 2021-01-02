<?php

namespace common\models;

use common\utils\NumberUtils;
use common\utils\StringUtils;
use common\utils\UUIDUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%coupon_batch}}".
 *
 * @property int $id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int $company_id 公司ID
 * @property string $batch_no 批次编号
 * @property string $name 批次名称
 * @property string $coupon_name 优惠券名称
 * @property int $startup 满金额
 * @property int $discount 减金额
 * @property int $type 优惠券类型
 * @property string|null $remark 备注
 * @property int $use_limit_type 优惠券限制类型 1全品类  2 限大类 3限品类 4指定商品
 * @property string|null $use_limit_type_params 优惠券限制参数
 * @property int|null $restore 可恢复 1可以恢复  2不可恢复
 * @property string $draw_start_time 领取开始时间
 * @property string $draw_end_time 领取结束时间
 * @property string|null $use_start_time 使用开始时间
 * @property string|null $use_end_time 使用结束时间
 * @property int $operator_id 操作人ID
 * @property string|null $operator_name 操作人姓名
 * @property int|null $status 0为已删除 1为有效  2为已停止
 * @property int $draw_limit_type 领取优惠券限制类型 1整个周期限制 2限制天 3限制周 4限制月 5限制年
 * @property int|null $draw_limit_type_params 领取优惠券限制参数
 * @property int $draw_amount 已领取数量
 * @property int $amount 总数量
 * @property int $version 版本号
 * @property int $is_public 是否是开放领取
 * @property int $draw_customer_type 限制客户类型  1不限制  2限制白名单 3限制黑名单
 * @property string|null $draw_customer_phones 限制客户手机号，逗号分隔
 * @property int $is_pop 首页弹窗
 * @property int $owner_type 归属类型
 * @property int $coupon_type 1普通优惠券 2新人优惠券
 * @property int $user_time_type 1定义区间 2当日 3次日
 * @property string|null $use_time_feature 新人优惠券使用规则，和copon_type对应
 * @property int $owner_id 归属id
 */
class CouponBatch extends \yii\db\ActiveRecord
{
    public $goods_owner;
    public $owner_name;
    public $big_sort;
    public $goods_id;
    public $sku_id;
    public $used_count;
    public $user_time_type_stat;
    public $user_time_type_end;
    public $user_time_days;
    public $use_limit_type_hidden ;


    const IS_POP_TRUE = 1;
    const IS_POP_FALSE = 0;

    public static $isPopArr = [
        self::IS_POP_FALSE =>'不弹',
        self::IS_POP_TRUE =>'弹窗',
    ];

    const USER_TIME_FEATURE_RANG = 1;
    const USER_TIME_FEATURE_CUR = 2;
    const USER_TIME_FEATURE_NEXT = 3;
    public static $userTimeType = [
        self::USER_TIME_FEATURE_RANG =>'自定义时间',
        self::USER_TIME_FEATURE_CUR =>'领券当日起',
        self::USER_TIME_FEATURE_NEXT =>'领券次日起',
    ];

    const COUPON_PLAN = 1;
    const COUPON_NEW = 2;
    public static $couponType = [
        self::COUPON_PLAN=>'普通优惠券',
        self::COUPON_NEW=>'新人优惠券'
    ];
    public static $couponTypeCssArr = [
        self::COUPON_PLAN =>'label label-info',
        self::COUPON_NEW =>'label label-success',
    ];

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;

    public static $statusArr = [
        self::STATUS_DELETED =>'已删除',
        self::STATUS_ACTIVE =>'已激活',
        self::STATUS_DISABLED =>'已停止',
    ];
    public static $statusDisplayArr = [
        self::STATUS_ACTIVE =>'已激活',
        self::STATUS_DISABLED =>'已停止',
    ];
    public static $statusCssArr = [
        self::STATUS_DELETED =>'label label-danger',
        self::STATUS_ACTIVE =>'label label-success',
        self::STATUS_DISABLED =>'label label-warning',
    ];

    const TRY_DRAW_RESULT_OK = 1;
    const TRY_DRAW_RESULT_OUT_STOCK = 2;
    const TRY_DRAW_RESULT_DRAWN = 3;
    const TRY_DRAW_RESULT_FORBIDDEN = 4;

    public static $drawStatusArr = [
        self::TRY_DRAW_RESULT_OK =>'可领取',
        self::TRY_DRAW_RESULT_OUT_STOCK =>'已抢完',
        self::TRY_DRAW_RESULT_DRAWN =>'已领取',
        self::TRY_DRAW_RESULT_FORBIDDEN =>'不可领',
    ];


    const DRAW_TYPE_LIMIT_ALL = 1;
    const DRAW_TYPE_LIMIT_DAY = 2;
    const DRAW_TYPE_LIMIT_WEEK = 3;
    const DRAW_TYPE_LIMIT_MONTH = 4;
    const DRAW_TYPE_LIMIT_YEAR = 5;

    public static $drawTypeLimitArr = [
        self::DRAW_TYPE_LIMIT_ALL =>'整个周期',
        self::DRAW_TYPE_LIMIT_DAY =>'每天限制',
        self::DRAW_TYPE_LIMIT_WEEK =>'每周限制',
        self::DRAW_TYPE_LIMIT_MONTH =>'每月限制',
        self::DRAW_TYPE_LIMIT_YEAR =>'每年限制',
    ];

    const PUBLIC_TRUE = 1;
    const PUBLIC_FALSE = 0;

    public static $isPublicArr = [
        self::PUBLIC_TRUE =>'开放领取',
        self::PUBLIC_FALSE =>'内部使用',
    ];

    public static $isPublicCssArr = [
        self::PUBLIC_TRUE =>'label label-danger',
        self::PUBLIC_FALSE =>'label label-success',
    ];

    const DRAW_CUSTOMER_TYPE_ALL = 1;
    const DRAW_CUSTOMER_TYPE_WHITE = 2;
    const DRAW_CUSTOMER_TYPE_BLACK = 3;

    public static $drawCustomerTypeArr = [
        self::DRAW_CUSTOMER_TYPE_ALL =>'不限制',
        self::DRAW_CUSTOMER_TYPE_WHITE =>'白名单领取',
        self::DRAW_CUSTOMER_TYPE_BLACK =>'黑名单不可领取',
    ];

    public static $drawCustomerTypeCssArr = [
        self::DRAW_CUSTOMER_TYPE_ALL =>'label label-danger',
        self::DRAW_CUSTOMER_TYPE_WHITE =>'label label-success',
        self::DRAW_CUSTOMER_TYPE_BLACK =>'label label-warning',
    ];

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
    public static function tableName()
    {
        return '{{%coupon_batch}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'draw_start_time', 'draw_end_time','use_time_feature'], 'safe'],
            [['company_id', 'type', 'use_limit_type', 'restore', 'operator_id',
                'status', 'draw_limit_type', 'draw_limit_type_params', 'draw_amount', 'amount', 'version',
                'is_public','draw_customer_type','is_pop','owner_type','owner_id','coupon_type'], 'integer'],
            [['name', 'coupon_name', 'startup', 'discount',
                'draw_start_time', 'draw_end_time',
                'operator_id', 'draw_limit_type_params','use_limit_type','amount',
                'is_public','draw_customer_type','owner_type','owner_id','coupon_type','user_time_type'],'required'],
            [['name', 'coupon_name'], 'string', 'max' => 1023],
            [['draw_customer_phones'], 'string', 'max' => 10240],
            [['remark', 'use_limit_type_params', 'operator_name','use_time_feature'], 'string', 'max' => 255],
            [['batch_no'], 'string', 'max' => 64],
            ['use_limit_type','default','value' => Coupon::LIMIT_TYPE_ALL],
            [[ 'draw_customer_type'], 'validateDrawCustomerType'],
            [[ 'draw_start_time', 'draw_end_time'], 'validateDateTime'],
            [[ 'use_limit_type'], 'validateUseLimitType'],
            [['coupon_type'],'validateUseTimeFeature'],
            ['batch_no','unique'],
            [['goods_owner','big_sort','goods_id','sku_id','user_time_type_stat','user_time_type_end','user_time_days'],'safe'],
            [['startup', 'discount'], 'number'],
            ['startup','default','value' => 0],
            [['discount','coupon_type','user_time_type'],'default','value' => 0],
            ['amount','default','value' => 1],
            ['amount','compare','compareValue' => 0,'operator' =>'>',"message" => '总数最少为1张'],
            ['discount','compare','compareAttribute' => 'startup','operator' =>'<',"message" => '满金额必须大于减金额','type' =>'number'],

        ];
    }

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if ($this->use_limit_type==Coupon::LIMIT_TYPE_ALL){

            }
            else if ($this->use_limit_type==Coupon::LIMIT_TYPE_OWNER){
                $this->use_limit_type_params = $this->goods_owner;
            }
            else if ($this->use_limit_type==Coupon::LIMIT_TYPE_SORT){
                $this->use_limit_type_params = $this->big_sort;
            }
            else if ($this->use_limit_type==Coupon::LIMIT_TYPE_GOODS_SKU){
                $this->use_limit_type_params = $this->sku_id;
            }
            if ($this->owner_type == GoodsConstantEnum::OWNER_SELF){
                $this->owner_id = GoodsConstantEnum::OWNER_SELF_ID;
            }
            if($this->user_time_type == self::USER_TIME_FEATURE_RANG){
                $this->use_time_feature = json_encode(['start_time'=>$this->user_time_type_stat,'end_time'=>$this->user_time_type_end]);
            }
            if($this->user_time_type == self::USER_TIME_FEATURE_CUR || $this->user_time_type == self::USER_TIME_FEATURE_NEXT){
                $this->use_time_feature = json_encode(['days'=>$this->user_time_days]);
            }

            if ($this->isNewRecord){
                $this->batch_no = UUIDUtils::uuidWithoutSeparator();
            }
            return true;
        } else {
            return false;
        }
    }

    public function validateUseTimeFeature($attribute, $params){
        if(StringUtils::isNotBlank($this->coupon_type)){
            if(StringUtils::isBlank($this->user_time_type)){
                $this->addError('user_time_type', '用券规则必选');
            }
            if($this->user_time_type == self::USER_TIME_FEATURE_RANG){
                if (StringUtils::isBlank($this->user_time_type_stat) || StringUtils::isBlank($this->user_time_type_end)){
                    $this->addError('user_time_type_stat', '开始时间必填');
                    $this->addError('user_time_type_end', '结束时间必填');  
                }
                if ($this->user_time_type_end<=$this->user_time_type_stat){
                     $this->addError('user_time_type_stat', '结束时间必须晚于开始时间');
                     $this->addError('user_time_type_end', '结束时间必须晚于开始时间');
                }
            }
            if($this->user_time_type > self::USER_TIME_FEATURE_RANG){
                if(StringUtils::isBlank($this->user_time_days) || !NumberUtils::isNumeric($this->user_time_days) || $this->user_time_days<1){
                    $this->addError('user_time_days', '必须位数字，至少为1');
                } 
            }
            if ($this->coupon_type==self::COUPON_NEW){
                if ($this->is_public!=self::PUBLIC_FALSE){
                    $this->addError('is_public', '新人优惠券必须内部使用');
                }
            }

        }
    }

    public function validateUseLimitType($attribute, $params)
    {
        if (!StringUtils::isBlank($this->use_limit_type)){
            if ($this->use_limit_type==Coupon::LIMIT_TYPE_ALL){
            }
            else if ($this->use_limit_type==Coupon::LIMIT_TYPE_OWNER){
                if (StringUtils::isBlank($this->goods_owner)){
                    $this->addError('goods_owner', '商品归属必填');
                }
            }
            else if ($this->use_limit_type==Coupon::LIMIT_TYPE_SORT){
                if (StringUtils::isBlank($this->goods_owner)){
                    $this->addError('goods_owner', '商品归属必填');
                }
                if (StringUtils::isBlank($this->big_sort)){
                    $this->addError('big_sort', '商品分类必填');
                }
            }
            else if ($this->use_limit_type==Coupon::LIMIT_TYPE_GOODS_SKU){
                if (StringUtils::isBlank($this->goods_owner)){
                    $this->addError('goods_owner', '商品归属必填');
                }
                if (StringUtils::isBlank($this->big_sort)){
                    $this->addError('big_sort', '商品分类必填');
                }
                if (StringUtils::isBlank($this->goods_id)){
                    $this->addError('goods_id', '商品必填');
                }
                if (StringUtils::isBlank($this->sku_id)){
                    $this->addError('sku_id', '商品属性必填');
                }
            }
            else{
                $this->addError('use_limit_type', '不支持的使用限制类型');
            }
        }
    }

    public function validateDateTime($attribute, $params)
    {
        if (!StringUtils::isBlank($this->draw_start_time)&&!StringUtils::isBlank($this->draw_end_time)){
            if ($this->draw_end_time<=$this->draw_start_time){
                $this->addError('draw_start_time', '结束时间必须晚于开始时间');
                $this->addError('draw_end_time', '结束时间必须晚于开始时间');
            }
        }
    }

    public function validateDrawCustomerType($attribute, $params)
    {
        if (in_array($this->draw_customer_type,[self::DRAW_CUSTOMER_TYPE_WHITE,self::DRAW_CUSTOMER_TYPE_BLACK])){
            if (StringUtils::isBlank($this->draw_customer_phones)){
                $this->addError('draw_customer_phones', '名单不能为空');
            }
        }
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
            'company_id' => '公司ID',
            'batch_no'=>'批次编号',
            'goods_owner'=>'商品归属',
            'big_sort'=>'商品分类',
            'goods_id'=>'商品名称',
            'sku_id'=>'商品属性名称',
            'name' => '批次名称',
            'coupon_name' => '优惠券名称',
            'startup' => '满金额',
            'discount' => '减金额',
            'type' => '折扣类型',
            'remark' => '备注',
            'use_limit_type' => '使用限制类型',
            'use_limit_type_hidden' => "使用限制类型隐藏域",
            'use_limit_type_params' => '使用限制参数',
            'restore' => '可否恢复',
            'draw_start_time' => '领取开始时间',
            'draw_end_time' => '领取结束时间',
            // 'use_start_time' => '使用开始时间',废弃
            // 'use_end_time' => '使用结束时间',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人姓名',
            'status' => '状态',
            'draw_limit_type' => '领取限制类型',
            'draw_limit_type_params' => '领取限制次数',
            'draw_amount' => '已领',
            'amount' => '总量',
            'version' => '版本号',
            'is_public'=>'是否是开放领取',
            'draw_customer_type'=>'限制客户类型',
            'draw_customer_phones'=>'限制客户手机号，逗号分隔',
            'used_count'=>'已使用',
            'is_pop'=>'是否弹窗',
            'owner_type'=>'归属类型',
            'owner_id'=>'归属id',
            'coupon_type' =>'优惠券类型',
            'use_time_feature' => '新人优惠券使用规则参数详情',
            'user_time_type' => '用券时间',
            'user_time_type_stat'=> '优惠券使用开始时间',
            'user_time_type_end' => '优惠券使用结束时间',
            'user_time_days'=> 'N日内可用',
        ];
    }

    public function restoreForm(){
        $this->startup = Common::showAmount($this->startup);
        $this->discount = Common::showAmount($this->discount);
        return $this;
    }

    public function storeForm(){
        $this->startup = Common::setAmount($this->startup);
        $this->discount = Common::setAmount($this->discount);
        return $this;
    }

    public function decodeUserTimeFeature($is_calc = false){
        $use_time_feature = json_decode($this->use_time_feature);
        $this->user_time_type_stat = $use_time_feature->start_time??'';
        $this->user_time_type_end = $use_time_feature->end_time??'';
        $this->user_time_days = $use_time_feature->days??0; 
        if($is_calc){
            if($this->user_time_type == self::USER_TIME_FEATURE_RANG){
                $this->use_start_time = $this->user_time_type_stat;
                $this->use_end_time = $this->user_time_type_end;
            }
            //当日开始时间=领取优惠券时间
            if($this->user_time_type == self::USER_TIME_FEATURE_CUR){
                $this->use_start_time = date('Y-m-d H:i:s');
                $this->use_end_time = date("Y-m-d H:i:s",strtotime("$this->use_start_time + $this->user_time_days day"));
            }
            // 次日开始时间=明日0点开始
            if($this->user_time_type == self::USER_TIME_FEATURE_NEXT){
                $this->use_start_time = date("Y-m-d H:i:s",strtotime(date('Y-m-d')."+1 day"));
                $this->use_end_time = date("Y-m-d H:i:s",strtotime("$this->use_start_time + $this->user_time_days day"));
            }
        }
    }

}
