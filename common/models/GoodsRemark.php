<?php

namespace common\models;

/**
 * This is the model class for table "{{%goods_remark}}".
 *
 * @property integer $id
 * @property integer $sku_id
 * @property integer $customer_id
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 */
class GoodsRemark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_remark}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku_id', 'customer_id'], 'required'],
            [['sku_id', 'customer_id', 'company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['remark'], 'string', 'max' => 4095],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku_id' => '属性ID',
            'customer_id' => '客户ID',
            'remark' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
        ];
    }
}
