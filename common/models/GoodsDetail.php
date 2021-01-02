<?php

namespace common\models;

/**
 * This is the model class for table "{{%goods_detail}}".
 *
 * @property integer $id
 * @property integer $goods_id
 * @property string $goods_detail
 * @property integer $company_id
 */
class GoodsDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'company_id'], 'integer'],
            [['goods_detail'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品ID',
            'goods_detail' => '商品详情',
            'company_id' => 'Company ID',
        ];
    }
}
