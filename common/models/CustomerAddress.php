<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_address}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $customer_id
 * @property string $name
 * @property string $phone
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $community
 * @property string $address
 * @property double $lat
 * @property double $lng
 * @property integer $is_default
 */
class CustomerAddress extends \yii\db\ActiveRecord
{
    const DEFAULT_TRUE = 1;
    const DEFAULT_FALSE = 0;

    public static $StatusArr=[
        self::DEFAULT_TRUE => '是默认',
        self::DEFAULT_FALSE => '非默认',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_address}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['customer_id', 'province_id', 'city_id', 'county_id', 'is_default'], 'integer'],
            [['customer_id','name', 'phone', 'province_id', 'city_id', 'county_id', 'address'], 'required'],
            [['lat', 'lng'], 'number'],
            [['phone','name'], 'string', 'max' => 20],
            [['community', 'address'], 'string', 'max' => 255],
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
            'customer_id' => '用户ID',
            'name' => '收货人姓名',
            'phone' => '电话',
            'province_id' => '省份',
            'city_id' => '城市',
            'county_id' => '县/区',
            'community' => '小区',
            'address' => '地址',
            'lat' => '纬度',
            'lng' => '经度',
            'is_default' => '是否是默认值',
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
