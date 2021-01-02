<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_balance}}".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $amount
 * @property string $created_at
 * @property string $updated_at
 * @property integer $freeze_amount
 * @property integer $version
 */
class CustomerBalance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_balance}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'amount', 'version','freeze_amount'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['customer_id'], 'unique'],
            [['amount','freeze_amount'],'default','value' => 0]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'amount' => '可用余额',
            'freeze_amount'=>'冻结余额',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'version' => '版本号',
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
}
