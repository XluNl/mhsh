<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%popularizer}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property string $phone
 * @property string $em_phone
 * @property string $wx_number
 * @property string $nickname
 * @property string $realname
 * @property string $occupation
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $community
 * @property string $address
 * @property double $lat
 * @property double $lng
 */
class Popularizer extends \yii\db\ActiveRecord
{

    public $province_text;
    public $city_text;
    public $county_text;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%popularizer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'phone', 'realname'], 'required'],
            [['user_id', 'status', 'company_id', 'province_id', 'city_id', 'county_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['lat', 'lng'], 'number'],
            [['phone', 'em_phone', 'wx_number', 'nickname', 'realname', 'occupation', 'community', 'address'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'company_id' => 'Company ID',
            'phone' => '手机号',
            'em_phone' => '紧急手机号',
            'wx_number' => '微信号',
            'nickname' => '昵称',
            'realname' => '姓名',
            'occupation' => '职业',
            'province_id' => '省',
            'city_id' => '市',
            'county_id' => '县/区',
            'community' => '社区',
            'address' => '具体地址',
            'lat' => '纬度',
            'lng' => '经度',
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

    public function getUserInfo() {
        return $this->hasOne(UserInfo::className(), ['id' => 'user_id']);
    }
}
