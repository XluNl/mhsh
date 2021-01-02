<?php

namespace common\models;

use common\utils\PriceUtils;
use common\utils\StringUtils;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%goods_sku}}".
 *
 * @property integer $id
 * @property integer $goods_id
 * @property string $sku_name
 * @property string $sku_img
 * @property string $sku_unit
 * @property integer $sku_standard
 * @property integer $sku_unit_factor
 * @property string $sku_describe
 * @property integer $sku_status
 * @property integer $sku_stock
 * @property integer $sku_sold
 * @property string $created_at
 * @property string $updated_at
 * @property integer $display_order
 * @property integer $company_id
 * @property integer $sale_price
 * @property integer $purchase_price
 * @property integer $reference_price
 * @property integer $share_rate_1
 * @property integer $share_rate_2
 * @property integer $delivery_rate
 * @property integer $features
 * @property string $production_date
 * @property string $expired_date
 * @property integer $one_level_rate
 * @property integer $two_level_rate
 * @property integer $agent_rate
 * @property integer $company_rate
 * @property integer $start_sale_num
 */
class GoodsSku extends ActiveRecord
{

    const SKU_STANDARD_TRUE  = 1;
    const SKU_STANDARD_FALSE  = 0;


    const FEATURES_NAME_EXPIRED_DATE = 'expired_date';

    public static $featuresNameArr = [
        self::FEATURES_NAME_EXPIRED_DATE=>'有效期',
    ];

    public static $skuStandardArr = [
       self::SKU_STANDARD_TRUE=>'标准品',
       self::SKU_STANDARD_FALSE=>'非标准品',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_sku}}';
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
            [['goods_id', 'sku_standard', 'sku_status', 'sku_stock', 'sku_sold', 'display_order', 'company_id','start_sale_num'], 'integer'],
            [['goods_id', 'sku_standard', 'sku_unit_factor','sku_stock', 'sku_sold', 'display_order','purchase_price','reference_price','share_rate_1','share_rate_2','delivery_rate','sku_name','sku_unit','one_level_rate','two_level_rate','agent_rate','company_rate','start_sale_num'], 'required'],
            [['created_at', 'updated_at','production_date','expired_date'], 'safe'],
            [['share_rate_1', 'share_rate_2', 'delivery_rate','agent_rate','purchase_price','sku_unit_factor', 'reference_price','sale_price'], 'number'],
            [['sku_name'], 'string', 'max' => 20],
            [['sku_img'], 'string', 'max' => 255],
            [['sku_unit'], 'string', 'max' => 10],
            [['sku_describe'], 'string', 'max' => 511],
            [['features'], 'string', 'max' => 4096],
            ['share_rate_1', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            ['share_rate_2', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            ['delivery_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            ['one_level_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            ['two_level_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            ['agent_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            ['company_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            [['purchase_price','reference_price'],'validatePrice'],
            [['share_rate_1','share_rate_2','delivery_rate','one_level_rate','two_level_rate','agent_rate','company_rate'],'validateRate'],
            [['sku_name'], 'unique', 'targetAttribute' => ['goods_id', 'sku_name'], 'message' => '属性名称不能重复'],
            ['start_sale_num', 'compare', 'compareValue' => 1, 'operator' => '>=','message' => '必须大于0'],
        ];
    }

    public function validatePrice($attribute,$params){
        if (PriceUtils::validateInput($this->$attribute) === false)
        {
            $this->addError($attribute, "最小精确到分(0.01)");
        }
    }

    public function validateRate($attribute, $params)
    {
        $paymentHandlingFeeRate= Yii::$app->params['payment.handling.fee.rate'];
        $share_rate_1 = StringUtils::isBlank($this->share_rate_1)?0:$this->share_rate_1;
        $share_rate_2 = StringUtils::isBlank($this->share_rate_2)?0:$this->share_rate_2;
        $delivery_rate = StringUtils::isBlank($this->delivery_rate)?0:$this->delivery_rate;
        $one_level_rate = StringUtils::isBlank($this->one_level_rate)?0:$this->one_level_rate;
        $two_level_rate = StringUtils::isBlank($this->two_level_rate)?0:$this->two_level_rate;
        $agent_rate = StringUtils::isBlank($this->agent_rate)?0:$this->agent_rate;
        $company_rate = StringUtils::isNotBlank($this->company_rate)?0:$this->company_rate;
        if ($share_rate_1+$share_rate_2+$delivery_rate+$one_level_rate+$two_level_rate+$agent_rate+$paymentHandlingFeeRate+$company_rate>10000){
            $msg = "分润比例总和需不大于".Common::showPercentWithUnit(10000-$paymentHandlingFeeRate);
            $this->addError('share_rate_1', $msg);
            $this->addError('share_rate_2', $msg);
            $this->addError('delivery_rate', $msg);
            $this->addError('one_level_rate', $msg);
            $this->addError('two_level_rate', $msg);
            $this->addError('agent_rate', $msg);
            $this->addError('company_rate', $msg);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品ID',
            'sku_name' => '属性名称',
            'sku_img' => '属性照片',
            'sku_unit' => '属性单位',
            'sku_standard' => '是否是标准件 1为标准品  0为非标准品',
            'sku_unit_factor' => '重量因子(单位：千克)',
            'sku_describe' => '属性描述',
            'sku_status' => '属性状态',
            'sku_stock'=> '属性库存',
            'sku_sold'=> '属性已售',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'display_order' => '排列顺序',
            'company_id' => 'Company ID',
            'sale_price'=>'默认售卖价',
            'purchase_price'=>'采购价',
            'reference_price'=>'划线价',
//            'share_rate_1'=>'一级分享比例(百分比)',
            'share_rate_1'=>'分享团长',
            'share_rate_2'=>'二级分享比例(百分比)',
//            'delivery_rate'=>'配送比例(百分比)',
            'delivery_rate'=>'配送团长',
            'features'=>'扩展字段',
            'production_date'=>'生产时间/有效期起始时间',
            'expired_date'=>'过期时间/有效期结束时间',
//            'one_level_rate'=>'用户一级分销比例',
            'one_level_rate'=>'一级用户',
//            'two_level_rate'=>'用户二级分销比例',
            'two_level_rate'=>'二级用户',
            'agent_rate'=>'代理商分润比例',
            'company_rate'=>'公司分销比例',
            'start_sale_num'=>'每单起售数量',
        ];
    }

    public function storeForm(){
        $this->sale_price = Common::setAmount($this->sale_price);
        $this->purchase_price = Common::setAmount($this->purchase_price);
        $this->reference_price = Common::setAmount($this->reference_price);
        $this->one_level_rate = Common::setPercent($this->one_level_rate);
        $this->two_level_rate = Common::setPercent($this->two_level_rate);
        $this->share_rate_1 = Common::setPercent($this->share_rate_1);
        $this->share_rate_2 = Common::setPercent($this->share_rate_2);
        $this->delivery_rate = Common::setPercent($this->delivery_rate);
        $this->agent_rate = Common::setPercent($this->agent_rate);
        $this->company_rate = Common::setPercent($this->company_rate);
        return $this;
    }

    public function restoreForm(){
        $this->sale_price = Common::showAmount($this->sale_price);
        $this->purchase_price = Common::showAmount($this->purchase_price);
        $this->reference_price = Common::showAmount($this->reference_price);
        $this->one_level_rate = Common::showPercent($this->one_level_rate);
        $this->two_level_rate = Common::showPercent($this->two_level_rate);
        $this->share_rate_1 = Common::showPercent($this->share_rate_1);
        $this->share_rate_2 = Common::showPercent($this->share_rate_2);
        $this->delivery_rate = Common::showPercent($this->delivery_rate);
        $this->agent_rate = Common::showPercent($this->agent_rate);
        $this->company_rate = Common::showPercent($this->company_rate);
        return $this;
    }

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if (StringUtils::isBlank($this->sku_status)){
                $this->sku_status = GoodsConstantEnum::STATUS_ACTIVE;
            }
            if ($this->sku_standard==GoodsSku::SKU_STANDARD_TRUE){
                $this->sku_unit_factor = 1;
            }
            return true;
        } else {
            return false;
        }
    }


    public function getStorageSkuMapping(){
        return $this->hasOne(StorageSkuMapping::className(),['sku_id' => 'id']);
    }

}
