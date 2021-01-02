<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%storage_sku_mapping}}".
 *
 * @property int $id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int $goods_id 商品ID
 * @property int $sku_id 属性ID
 * @property int $company_id 公司ID
 * @property int $storage_sku_id 仓库商品属性ID
 * @property number $storage_sku_num 仓库映射数量
 */
class StorageSkuMapping extends \yii\db\ActiveRecord
{
    public $storage_sku_name;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%storage_sku_mapping}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['goods_id', 'sku_id', 'storage_sku_id','storage_sku_num'], 'required'],
            [['goods_id', 'sku_id', 'company_id', 'storage_sku_id'], 'integer'],
            [['storage_sku_num'],'number'],
            [['sku_id'], 'unique','message'=>'不能重复绑定'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'goods_id' => '商品ID',
            'sku_id' => '属性ID',
            'company_id' => '公司ID',
            'storage_sku_id' => '仓库商品属性ID',
            'storage_sku_num'=>'仓库映射数量',
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
