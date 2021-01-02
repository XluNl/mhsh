<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%delivery_freight}}".
 *
 * @property integer $id
 * @property integer $delivery_id
 * @property integer $line
 * @property integer $amount
 * @property integer $type
 * @property integer $company_id
 * @property string $created_at
 * @property string $updated_at
 */
class DeliveryFreight extends \yii\db\ActiveRecord
{

    const FREIGHT_TYPE_AMOUNT = 1;
    const FREIGHT_TYPE_DISTANCE = 2;

    public static $freightTypeArr=[
        self::FREIGHT_TYPE_AMOUNT=>'金额方案',
        self::FREIGHT_TYPE_DISTANCE=>'距离方案',
    ];
    public static $freightTypeCssArr=[
        self::FREIGHT_TYPE_AMOUNT=>'label label-success',
        self::FREIGHT_TYPE_DISTANCE=>'label label-info',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_freight}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delivery_id', 'line', 'amount', 'type', 'created_at'], 'required'],
            [['delivery_id', 'line', 'amount', 'type', 'company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'delivery_id' => '代送点ID',
            'line' => '基准线',
            'amount' => '金额',
            'type' => '运费方案类型',
            'company_id' => 'Company ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
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
