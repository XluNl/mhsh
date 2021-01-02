<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%goods_sku_stock}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $type
 * @property integer $num
 * @property integer $operator_role
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $goods_id
 * @property integer $schedule_id
 * @property integer $sku_id
 * @property string $remark
 */
class GoodsSkuStock extends \yii\db\ActiveRecord
{
    public $goods_owner;

    const TYPE_PURCHASING_IN = 1;
    const TYPE_GOODS_OUT = 2;
    const TYPE_LOSS = 3;

    public static $typeArr = [
        self::TYPE_PURCHASING_IN=>'采购入库',
        self::TYPE_GOODS_OUT=>'商品出库',
        self::TYPE_LOSS=>'报损',
    ];

    public static $typeCssArr=[
        self::TYPE_PURCHASING_IN=>'label label-success',
        self::TYPE_GOODS_OUT=>'label label-warning',
        self::TYPE_LOSS=>'label label-danger',
    ];

    public static $outArr = [
        self::TYPE_GOODS_OUT=>'商品出库',
        self::TYPE_LOSS=>'报损',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_sku_stock}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'type', 'num', 'operator_id', 'goods_id', 'sku_id','schedule_id'], 'integer'],
            [['type', 'operator_id', 'operator_name', 'goods_id', 'sku_id','schedule_id'], 'required'],
            [['operator_name'], 'string', 'max' => 255],
            [['remark'], 'string', 'max' => 511],
            ['num', 'compare', 'compareValue' => 0, 'operator' => '>'],
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
            'updated_at' => '修改时间',
            'company_id' => '公司ID',
            'type' => '类型',
            'num' => '数量',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人名称',
            'goods_id' => '商品',
            'sku_id' => '商品属性',
            'schedule_id' => '排期',
            'goods_owner'=>'商品归属',
            'remark'=>'备注',
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
        return $this->hasOne(GoodsSku::className(),['id' => 'sku_id']);
    }

    public function getGoods(){
        return $this->hasOne(Goods::className(),['id' => 'goods_id']);
    }
}
