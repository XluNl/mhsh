<?php

namespace common\models;

use common\utils\PriceUtils;
use common\utils\StringUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%goods_schedule}}".
 *
 * @property integer $id
 * @property integer $goods_id
 * @property integer $sku_id
 * @property integer $price
 * @property string $schedule_name
 * @property integer $schedule_status
 * @property integer $schedule_stock
 * @property integer $schedule_sold
 * @property integer $schedule_limit_quantity
 * @property integer $display_order
 * @property integer $schedule_display_channel
 * @property string $display_start
 * @property string $display_end
 * @property string $online_time
 * @property string $offline_time
 * @property string $expect_arrive_time
 * @property string $validity_start
 * @property string $validity_end
 * @property integer $operation_id
 * @property string $operation_name
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $collection_id
 * @property integer $owner_type
 * @property integer $owner_id
 * @property integer $storage_sku_id
 * @property-read mixed $goodsSku
 * @property-read mixed $goods
 * @property number $storage_sku_num
 * @property integer $recommend
 */
class GoodsSchedule extends ActiveRecord
{
    public $goods_owner;
    public $coupon_batch_count;
    const DISPLAY_NONE = 1;
    const DISPLAY_DISPLAY = 2;
    const DISPLAY_SALE = 3;

    const DISPLAY_STATUS_WAITING = 1;
    const DISPLAY_STATUS_IN_SALE = 2;
    const DISPLAY_STATUS_SUSPEND = 3;
    const DISPLAY_STATUS_END = 4;

    public static $displayStatusTextArr = [
        self::DISPLAY_STATUS_WAITING => '未开始',
        self::DISPLAY_STATUS_IN_SALE => '销售中',
        self::DISPLAY_STATUS_SUSPEND => '已停止',
        self::DISPLAY_STATUS_END => '已结束',
    ];


    const IS_RECOMMEND_TRUE = 1;
    const IS_RECOMMEND_FALSE = 0;

    public static $isRecommendArr = [
        self::IS_RECOMMEND_TRUE =>'推荐',
        self::IS_RECOMMEND_FALSE =>'隐藏',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_schedule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'sku_id', 'schedule_status', 'schedule_stock', 'schedule_limit_quantity', 'display_order', 'schedule_display_channel', 'operation_id', 'company_id','schedule_sold','collection_id','owner_type','owner_id','storage_sku_id','recommend'], 'integer'],
            [['goods_id', 'sku_id','price', 'schedule_stock', 'schedule_limit_quantity', 'schedule_display_channel', 'display_start', 'display_end', 'online_time', 'offline_time','expect_arrive_time','display_order','owner_type','owner_id'], 'required'],
            ['schedule_name','required','on'=>'backend'],
            [['display_start', 'display_end','validity_start', 'validity_end', 'online_time', 'offline_time', 'created_at', 'updated_at','expect_arrive_time'], 'safe'],
            [['operation_name','schedule_name'], 'string', 'max' => 255],
            [['price','storage_sku_num'], 'number'],
            [['display_start', 'display_end','validity_start', 'validity_end', 'online_time', 'offline_time','expect_arrive_time'], 'validateDateTime'],
            [['display_start', 'display_end','validity_start', 'validity_end', 'online_time', 'offline_time'],'match', 'pattern' => '/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/','message' => '{attribute}不能为空'],
            [['expect_arrive_time'],'match', 'pattern' => '/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/'],
            [['schedule_stock', 'schedule_limit_quantity'], 'integer'],
            ['schedule_status', 'in', 'range' => array_keys(GoodsConstantEnum::$statusArr),'message' => '{attribute}不合法'],
            ['goods_owner', 'in', 'range' => array_keys(GoodsConstantEnum::$ownerArr),'message' => '{attribute}不合法'],
            ['schedule_display_channel', 'in', 'range' =>array_keys(GoodsConstantEnum::$scheduleDisplayChannelArr),'message' => '{attribute}不合法'],
            [['price'], 'validatePrice'],
        ];
    }

    public function validateDateTime($attribute, $params)
    {
        if (!StringUtils::isBlank($this->display_start)&&!StringUtils::isBlank($this->display_end)){
            if ($this->display_end<=$this->display_start){
                $this->addError('display_start', '结束时间必须晚于开始时间');
                $this->addError('display_end', '结束时间必须晚于开始时间');
            }
        }
        if (!StringUtils::isBlank($this->online_time)&&!StringUtils::isBlank($this->offline_time)){
            if ($this->offline_time<=$this->online_time){
                $this->addError('online_time', '结束时间必须晚于开始时间');
                $this->addError('offline_time', '结束时间必须晚于开始时间');
            }
        }
        if (!StringUtils::isBlank($this->validity_start)&&!StringUtils::isBlank($this->validity_end)){
            if ($this->validity_end<=$this->validity_start){
                $this->addError('validity_start', '结束时间必须晚于开始时间');
                $this->addError('validity_end', '结束时间必须晚于开始时间');
            }
        }
        if (!StringUtils::isBlank($this->display_start)&&!StringUtils::isBlank($this->online_time)){
            if ($this->online_time<$this->display_start){
                $this->addError('online_time', '售卖开始时间必须不早于展示开始时间');
                $this->addError('display_start', '售卖开始时间必须不早于展示开始时间');
            }
        }
        if (!StringUtils::isBlank($this->display_end)&&!StringUtils::isBlank($this->offline_time)){
            if ($this->display_end<$this->offline_time){
                $this->addError('display_end', '售卖结束时间必须不晚于展示结束时间');
                $this->addError('offline_time', '售卖结束时间必须不晚于展示结束时间');
            }
        }
        if (!StringUtils::isBlank($this->expect_arrive_time)&&!StringUtils::isBlank($this->offline_time)){
            if ($this->expect_arrive_time<$this->offline_time){
                $this->addError('offline_time', '预计送达时间晚于结束售卖时间');
                $this->addError('expect_arrive_time', '预计送达时间晚于结束售卖时间');
            }
        }
    }


    public function validatePrice($attribute,$params){
        if (PriceUtils::validateInput($this->$attribute) === false)
        {
            $this->addError($attribute, "最小精确到分(0.01)");
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_owner'=>'商品归属',
            'goods_id' => '商品',
            'sku_id' => '属性',
            'price' => '活动价格',
            'schedule_name'=>'排期名称',
            'schedule_status' => '活动状态',
            'schedule_stock' => '活动库存',
            'schedule_sold'=> '已售数量',
            'schedule_limit_quantity' => '限购数量',
            'display_order' => '排序',
            'schedule_display_channel' => '展示模块',
            'display_start' => '展示开始时间',
            'display_end' => '展示结束时间',
            'online_time' => '起售时间',
            'offline_time' => '止售时间',
            'expect_arrive_time' => '预计送达时间',
            'validity_start' => '有效期起始时间',
            'validity_end' => '有效期截止时间',
            'operation_id' => '操作人ID',
            'operation_name' => '操作人名称',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'coupon_batch_count'=>'优惠券批次命中数',
            'collection_id'=>'排期id',
            'owner_type'=>'归属类型',
            'owner_id'=>'归属id',
            'storage_sku_id'=>'仓库商品属性ID',
            'storage_sku_num'=>'仓库映射数量',
            'recommend'=>'首页推荐',
        ];
    }

    public function restoreForm(){
        $this->price = Common::showAmount($this->price);
        return $this;
    }


    public function hasStorageMapping(){
        return StringUtils::isNotBlank($this->storage_sku_id)&&$this->storage_sku_id>0;
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

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if (StringUtils::isBlank($this->schedule_status)) {
                $this->schedule_status = GoodsConstantEnum::STATUS_ACTIVE;
            }
            return true;
        } else {
            return false;
        }
    }

    public function getGoodsSku(){
        return $this->hasOne(GoodsSku::className(),['id' => 'sku_id'])
            ->where(['sku_status'=>GoodsConstantEnum::$activeStatusArr]);
    }

    public function getGoods(){
        return $this->hasOne(Goods::className(),['id' => 'goods_id'])
            ->where(['goods_status'=>GoodsConstantEnum::$activeStatusArr]);
    }

    public function getGoodsDetail(){
        return $this->hasOne(GoodsDetail::className(),['goods_id' => 'goods_id']);
    }
}
