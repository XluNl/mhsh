<?php

namespace common\models;
use common\utils\StringUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%goods}}".
 *
 * @property int $id
 * @property string|null $goods_name 商品名称
 * @property string|null $goods_img 商品照片
 * @property string|null $goods_describe 商品描述
 * @property int $sort_1 一级分类
 * @property int|null $sort_2 二级分类
 * @property int $goods_status 商品状态
 * @property int $display_order 排列顺序
 * @property int $supplier_id 供应商ID
 * @property int $goods_sold_channel_type 商品售卖渠道 0 代理商级别 1配送点级别
 * @property int $goods_type 商品类型 1实物 2虚拟 3快递
 * @property int $goods_owner 归属类型 1自营类 2异业联盟
 * @property int $goods_owner_id 归属类型ID
 * @property int $goods_cart 1可加购物车  2不可加购物车
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int $company_id
 * @property string|null $goods_images 商品多图
 * @property-read mixed $goodsSku
 * @property-read mixed $goodsSkuAndStorageSkuMapping
 * @property string|null $goods_video 商品视频
 */
class Goods extends ActiveRecord {

    public $imageFile;
    public $videoFile;


	const STATUS_DELETED = 0;
    const STATUS_UP = 1;
    const STATUS_DOWN = 2;
    const STATUS_NEW = 3;

	const GOODS_SOLD_CHANNEL_TYPE_AGENT = 0;
    const GOODS_SOLD_CHANNEL_TYPE_DELIVERY = 1;

    public static $goodsSoldChannelTypeArr=[
        self::GOODS_SOLD_CHANNEL_TYPE_AGENT=>'代理商级别',
        self::GOODS_SOLD_CHANNEL_TYPE_DELIVERY=>'配送点级别'
    ];


    const GOODS_CART_TRUE = 1;
    const GOODS_CART_FALSE = 2;

    public static $goodsCartArr=[
        self::GOODS_CART_TRUE=>'可加购物车',
        self::GOODS_CART_FALSE=>'不可加购物车',
    ];

    public static $goodsTypeToCart=[
        GoodsConstantEnum::OWNER_SELF=>[
            GoodsConstantEnum::TYPE_OBJECT=>self::GOODS_CART_TRUE,
            GoodsConstantEnum::TYPE_VIRTUAL=>self::GOODS_CART_FALSE,
            GoodsConstantEnum::TYPE_EXPRESS=>self::GOODS_CART_FALSE,
        ],
        GoodsConstantEnum::OWNER_HA=>[
            GoodsConstantEnum::TYPE_OBJECT=>self::GOODS_CART_FALSE,
            GoodsConstantEnum::TYPE_VIRTUAL=>self::GOODS_CART_FALSE,
            GoodsConstantEnum::TYPE_EXPRESS=>self::GOODS_CART_FALSE,
        ],
        GoodsConstantEnum::OWNER_DELIVERY=>[
            GoodsConstantEnum::TYPE_OBJECT=>self::GOODS_CART_TRUE,
            GoodsConstantEnum::TYPE_VIRTUAL=>self::GOODS_CART_FALSE,
            GoodsConstantEnum::TYPE_EXPRESS=>self::GOODS_CART_FALSE,
        ],
    ];




	public static function tableName() {
		return "{{%goods}}";
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
    public function rules()
    {
        return [
            [['sort_1', 'sort_2', 'goods_status', 'display_order', 'supplier_id', 'goods_sold_channel_type', 'goods_type', 'goods_owner', 'goods_owner_id', 'goods_cart', 'company_id'], 'integer'],
            [['goods_status'], 'required'],
            [['sort_1', 'sort_2','goods_owner','goods_name','goods_img','display_order'], 'required'],
            ['goods_owner', 'default', 'value' => GoodsConstantEnum::OWNER_SELF],
            [['created_at', 'updated_at'], 'safe'],
            [['goods_name'], 'string', 'max' => 20],
            [['goods_img'], 'string', 'max' => 255],
            [['goods_describe'], 'string', 'max' => 511],
            [['goods_images', 'goods_video'], 'string', 'max' => 8192],
            [['imageFile'], 'file', 'extensions' => 'png, jpg', 'mimeTypes' => 'image/jpeg, image/png'],
            [['videoFile'], 'file', 'skipOnEmpty' => true,  'mimeTypes' => 'video/*'],
            [['goods_name'], 'unique', 'targetAttribute' => ['goods_name', 'company_id','goods_owner','goods_owner_id'], 'message' => '商品名称不能重复'],
        ];
    }

	public function attributeLabels() {
		return [
            'id' => '商品ID',
            'goods_name' => '商品名称',
            'goods_img' => '商品照片',
            'goods_describe' => '商品描述',
            'sort_1' => '一级分类',
            'sort_2' => '二级分类',
            'goods_status' => '商品状态 ',
            'display_order' => '排列顺序',
            'supplier_id'=>'供应商ID',
            'goods_sold_channel_type'=>'商品售卖渠道',
            'goods_type'=>'商品类别',
            'goods_cart'=>'可否加购物车',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'goods_owner' => '商品归属',
            'goods_owner_id'=>'商品归属ID',
            'company_id' => 'Company ID',
            'goods_images' => '商品多图',
            'goods_video' => '商品视频',
		];
	}

	public function beforeSave($insert) {
	    if (parent::beforeSave($insert)) {
            $this->goods_cart = self::$goodsTypeToCart[$this->goods_owner][$this->goods_type];
            if (StringUtils::isBlank($this->goods_status)){
                $this->goods_status = GoodsConstantEnum::STATUS_ACTIVE;
            }
	        return true;
	    } else {
	        return false;
	    }
	}

    public function getGoodsSku(){
        return $this->hasMany(GoodsSku::className(),['goods_id' => 'id'])
            ->where(['sku_status'=>GoodsConstantEnum::$activeStatusArr]);
    }



    public function getGoodsSkuAndStorageSkuMapping(){
        return $this->hasMany(GoodsSku::className(),['goods_id' => 'id'])
            ->where(['sku_status'=>GoodsConstantEnum::$activeStatusArr])
            ->with("storageSkuMapping");
    }



}