<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%delivery_comment}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $user_id
 * @property integer $delivery_id
 * @property integer $goods_id
 * @property integer $sku_id
 * @property string $images
 * @property string $content
 * @property integer $status
 * @property integer $is_show
 * @property integer $operator_id
 * @property string $operator_name
 */
class DeliveryComment extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_APPLY = 1 ;
    const STATUS_ACCEPT = 2;
    const STATUS_DENY = 3;

    public static $statusArr=[
        self::STATUS_APPLY =>'待审核',
        self::STATUS_ACCEPT =>'审核通过',
        self::STATUS_DENY =>'审核拒绝',
    ];
    public static $statusArrCss=[
        self::STATUS_APPLY =>'label label-info',
        self::STATUS_ACCEPT =>'label label-success',
        self::STATUS_DENY =>'label label-danger',
    ];

    const IS_SHOW_TRUE = 1;
    const IS_SHOW_FALSE = 0;
    public static $isShowArr=[
        self::IS_SHOW_TRUE =>'显示',
        self::IS_SHOW_FALSE =>'隐藏',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'delivery_id', 'goods_id', 'sku_id', 'status', 'is_show', 'operator_id','user_id'], 'integer'],
            [['delivery_id', 'goods_id', 'sku_id', 'images', 'content'], 'required'],
            [['images', 'content'], 'string', 'max' => 8191],
            [['operator_name'], 'string', 'max' => 255],
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
            'company_id' => '公司ID',
            'user_id' =>'用户id',
            'delivery_id' => '配送点ID',
            'goods_id' => '商品ID',
            'sku_id' => '属性ID',
            'images' => '图片，多图',
            'content' => '文字',
            'status' => '审核状态',
            'is_show' => '是否显示',
            'operator_id' => '审核人id',
            'operator_name' => '审核人名称',
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

    public function getGoodsSku(){
        return $this->hasOne(GoodsSku::className(),['id' => 'sku_id'])
            ->where(['sku_status'=>GoodsConstantEnum::$activeStatusArr]);
    }

    public function getGoods(){
        return $this->hasOne(Goods::className(),['id' => 'goods_id'])
            ->where(['goods_status'=>GoodsConstantEnum::$activeStatusArr]);
    }

    public function getDelivery(){
        return $this->hasOne(Delivery::className(),['id' => 'delivery_id']);
    }
}
