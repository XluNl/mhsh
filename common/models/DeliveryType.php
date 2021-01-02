<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%delivery_type}}".
 *
 * @property integer $id
 * @property integer $delivery_id
 * @property integer $delivery_type
 * @property string $params
 * @property integer $status
 * @property integer $company_id
 * @property string $created_at
 * @property string $updated_at
 */
class DeliveryType extends \yii\db\ActiveRecord
{

    const FREIGHT_TYPE_FIX = 0;
    const FREIGHT_TYPE_AMOUNT = 1;
    const FREIGHT_TYPE_DISTANCE = 2;

    public static $freightTypeArr=[
        self::FREIGHT_TYPE_FIX=>'固定金额方案',
        self::FREIGHT_TYPE_AMOUNT=>'金额方案',
        self::FREIGHT_TYPE_DISTANCE=>'距离方案',
    ];
    public static $freightTypeCssArr=[
        self::FREIGHT_TYPE_FIX=>'label label-primary',
        self::FREIGHT_TYPE_AMOUNT=>'label label-success',
        self::FREIGHT_TYPE_DISTANCE=>'label label-info',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delivery_id', 'delivery_type'], 'required'],
            [['delivery_id', 'delivery_type', 'status', 'company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            ['params','number']
            //[['params'], 'string', 'max' => 4096],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'delivery_id' => '配送点ID',
            'delivery_type' => '配送方式',
            'params' => '配送费用',
            'status' => '状态',
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

    public function restoreForm(){
        $this->params = Common::showAmount($this->params);
        return $this;
    }
}
