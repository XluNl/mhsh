<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%goods_sold_channel}}".
 *
 * @property integer $id
 * @property integer $goods_id
 * @property integer $sold_channel_biz_id
 * @property string $created_at
 * @property integer $company_id
 */
class GoodsSoldChannel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_sold_channel}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'sold_channel_biz_id', 'company_id'], 'integer'],
            [['created_at'], 'safe'],
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
            'sold_channel_biz_id' => '业务ID',
            'created_at' => '创建时间',
            'company_id' => 'Company ID',
        ];
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}
