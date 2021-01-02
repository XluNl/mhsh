<?php

namespace common\models;

use common\utils\PriceUtils;
use common\utils\StringUtils;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "sptx_goods_sku_alliance".
 *
 * @property integer $id
 * @property integer $goods_id
 * @property string $goods_name
 * @property string $goods_img
 * @property string $goods_detail
 * @property integer $goods_type
 * @property integer $display_channel
 * @property integer $goods_owner_type
 * @property integer $goods_owner_id
 * @property integer $sku_id
 * @property string $sku_name
 * @property string $sku_img
 * @property string $sku_unit
 * @property string $sku_describe
 * @property integer $sku_status
 * @property integer $sku_stock
 * @property integer $display_order
 * @property integer $sale_price
 * @property integer $purchase_price
 * @property integer $reference_price
 * @property integer $start_sale_num
 * @property string $features
 * @property string $production_date
 * @property string $expired_date
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $audit_status
 * @property string $audit_result
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $share_rate_1
 * @property integer $share_rate_2
 * @property integer $one_level_rate
 * @property integer $two_level_rate
 * @property integer $delivery_rate
 * @property integer $agent_rate
 * @property integer $company_rate
 * @property integer $sort_1
 * @property integer $sort_2
 * @property string $expect_offline_time
 * @property string $expect_arrive_time
 */
class GoodsSkuAlliance extends \yii\db\ActiveRecord
{


    public $goods_img_text;
    public $sku_img_text;
    public $goods_detail_text;
    public $audit_status_text;

    const AUDIT_STATUS_EDIT = 1;
    const AUDIT_STATUS_WAITING = 2;
    const AUDIT_STATUS_ACCEPT = 3;
    const AUDIT_STATUS_DENY = 4;
    const AUDIT_STATUS_PUBLISH = 5;

    /**
     * 商家端展示的状态
     * @var array
     */
    public static $showAuditStatusArr = [
        self::AUDIT_STATUS_EDIT,
        self::AUDIT_STATUS_WAITING,
        self::AUDIT_STATUS_ACCEPT,
        self::AUDIT_STATUS_DENY
    ];

    public static $auditStatusArr = [
        self::AUDIT_STATUS_EDIT=>'待提交',
        self::AUDIT_STATUS_WAITING=>'待审核',
        self::AUDIT_STATUS_ACCEPT=>'审核通过',
        self::AUDIT_STATUS_DENY=>'审核拒绝',
        self::AUDIT_STATUS_PUBLISH=>'已发布',
    ];

    public static $auditStatusCssArr = [
        self::AUDIT_STATUS_EDIT=>'label label-info',
        self::AUDIT_STATUS_WAITING=>'label label-primary',
        self::AUDIT_STATUS_ACCEPT=>'label label-success',
        self::AUDIT_STATUS_DENY=>'label label-danger',
        self::AUDIT_STATUS_PUBLISH=>'label label-warning',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_sku_alliance}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'goods_owner_id', 'sku_id', 'sku_stock', 'display_order',
                'sale_price', 'purchase_price', 'reference_price', 'company_id',
                'audit_status', 'operator_id','sku_status','start_sale_num','goods_owner_type',
                'display_channel','share_rate_1','share_rate_2','one_level_rate',
                'two_level_rate','delivery_rate','agent_rate','company_rate','sort_1','sort_2'], 'integer'],
            [['goods_detail'], 'string'],
            [['goods_name', 'goods_img', 'goods_type', 'sku_name','sku_stock', 'sku_unit', 'sku_status','goods_type','sku_describe', 'purchase_price', 'reference_price','goods_owner_type'], 'required'],
            [['production_date', 'expired_date', 'created_at', 'updated_at','expect_offline_time','expect_arrive_time'], 'safe'],
            [['goods_name', 'sku_name'], 'string', 'max' => 20],
            [['goods_img', 'sku_img', 'audit_result', 'operator_name'], 'string', 'max' => 255],
            [['sku_unit'], 'string', 'max' => 10],
            [['sku_describe'], 'string', 'max' => 50],
            [['features'], 'string', 'max' => 4096],
            [['sale_price','display_channel'],'required','on'=>'delivery'],
            [['production_date','expired_date'],'required','on'=>'alliance'],
           // [['production_date','expired_date','expect_offline_time','expect_arrive_time'],'required','on'=>'alliance'],
            [['company_rate','one_level_rate','two_level_rate','sort_1','sort_2'],'required','on'=>'delivery'],
            [['purchase_price', 'reference_price'],'validatePrice'],
            [['sale_price'],'validatePrice','on'=>'delivery'],
            //['share_rate_1', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100','on'=>'delivery'],
            //['share_rate_2', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100','on'=>'delivery'],
            //['delivery_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100','on'=>'delivery'],
            ['one_level_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100','on'=>'delivery'],
            ['two_level_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100','on'=>'delivery'],
           // ['agent_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            ['company_rate', 'compare', 'compareValue' => 10000, 'operator' => '<=','message' => '必须小于100'],
            [['share_rate_1','share_rate_2','delivery_rate','one_level_rate','two_level_rate','agent_rate','company_rate'],'validateRate'],
        ];
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
            //$this->addError('share_rate_1', $msg);
            //$this->addError('share_rate_2', $msg);
            //$this->addError('delivery_rate', $msg);
            $this->addError('one_level_rate', $msg);
            $this->addError('two_level_rate', $msg);
            //$this->addError('agent_rate', $msg);
            $this->addError('company_rate', $msg);
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
            'goods_id' => '商品ID',
            'sort_1' => '商品一级分类',
            'sort_2' => '商品二级分类',
            'goods_name' => '商品名称',
            'goods_img' => '商品照片',
            'goods_detail' => '商品描述',
            'goods_type' => '商品类型',
            'display_channel'=> '展示模块',
            'goods_owner_type' => '归属类型',
            'goods_owner_id' => '归属类型ID',
            'sku_id' => '属性id',
            'sku_name' => '属性名称',
            'sku_img' => '属性照片',
            'sku_unit' => '属性单位',
            'sku_describe' => '属性描述',
            'sku_status' => '属性状态 ',
            'sku_stock' => '库存',
            'display_order' => '排序',
            'sale_price'=>'售卖价',
            'purchase_price' => '采购价',
            'reference_price' => '划线价',
            'features' => '扩展字段',
            'production_date' => '生产时间/有效期起始时间',
            'expired_date' => '过期时间/有效期结束时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'audit_status' => '审核状态',
            'audit_result' => '审核结果',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人姓名',
            'start_sale_num'=>'每单起售数量',
            'expect_offline_time'=>'预计截单日期',
            'expect_arrive_time'=>'预计送达日期',
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

    public function getAlliance(){
        return $this->hasOne(Alliance::className(),['id' => 'goods_owner_id']);
    }

    public function getDelivery(){
        return $this->hasOne(Delivery::className(),['id' => 'goods_owner_id']);
    }

    public function restoreForm(){
        $this->sale_price = Common::showAmount($this->sale_price);
        $this->purchase_price = Common::showAmount($this->purchase_price);
        $this->reference_price = Common::showAmount($this->reference_price);
        $this->one_level_rate = Common::showPercent($this->one_level_rate);
        $this->two_level_rate = Common::showPercent($this->two_level_rate);
        $this->share_rate_1 = Common::showPercent($this->share_rate_1);
        //$this->share_rate_2 = Common::showPercent($this->share_rate_2);
        //$this->delivery_rate = Common::showPercent($this->delivery_rate);
        //$this->agent_rate = Common::showPercent($this->agent_rate);
        $this->company_rate = Common::showPercent($this->company_rate);
        return $this;
    }

    public function storeForm(){
        $this->sale_price = StringUtils::isBlank($this->sale_price)?0:Common::setAmount($this->sale_price);
        $this->purchase_price = StringUtils::isBlank($this->sale_price)?0:Common::setAmount($this->purchase_price);
        $this->reference_price = StringUtils::isBlank($this->sale_price)?0:Common::setAmount($this->reference_price);
        $this->one_level_rate = StringUtils::isBlank($this->one_level_rate)?0:Common::setPercent($this->one_level_rate);
        $this->two_level_rate = StringUtils::isBlank($this->two_level_rate)?0:Common::setPercent($this->two_level_rate);
        $this->share_rate_1 = StringUtils::isBlank($this->share_rate_1)?0:Common::setPercent($this->share_rate_1);
        //$this->share_rate_2 = StringUtils::isBlank($this->share_rate_2)?0:Common::setPercent($this->share_rate_2);
        //$this->delivery_rate = StringUtils::isBlank($this->delivery_rate)?0:Common::setPercent($this->delivery_rate);
        //$this->agent_rate = StringUtils::isBlank($this->agent_rate)?0:Common::setPercent($this->agent_rate);
        $this->company_rate = StringUtils::isBlank($this->company_rate)?0:Common::setPercent($this->company_rate);
        return $this;
    }
}
