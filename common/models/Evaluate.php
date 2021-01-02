<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%evaluate}}".
 *
 * @property string $id
 * @property integer $customer_id
 * @property string $order_no
 * @property integer $v1
 * @property integer $v2
 * @property string $statement
 * @property string $created_at
 * @property integer $company_id
 */
class Evaluate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%evaluate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'v1', 'v2', 'company_id'], 'integer'],
            [['order_no'], 'string', 'max' => 20],
            [['created_at'], 'safe'],
            [['statement'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => '用户ID',
            'order_no' => '订单号',
            'v1' => '商品质量',
            'v2' => '送达时间',
            'statement' => '评论',
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
