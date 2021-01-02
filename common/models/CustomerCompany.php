<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_company}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $user_id
 * @property integer $company_id
 */
class CustomerCompany extends \yii\db\ActiveRecord
{
    public $phone;
    public $status;
    public $province_id;
    public $city_id;
    public $county_id;
    public $lat;
    public $lng;
    public $is_customer;
    public $is_popularizer;
    public $is_delivery;
    public $em_phone;
    public $wx_number;
    public $email;
    public $nickname;
    public $realname;
    public $occupation;
    public $community;
    public $address;
    public $province_text;
    public $city_text;
    public $county_text;
    public $is_alliance;
    public $customer_id;
    public $name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_company}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['user_id'], 'required'],
            [['user_id', 'company_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
//    public function attributeLabels()
//    {
//        return [
//            'id' => 'ID',
//            'created_at' => 'Created At',
//            'updated_at' => 'Updated At',
//            'user_id' => 'User ID',
//            'company_id' => 'Company ID',
//        ];
//    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'phone' => '手机号',
            'em_phone' => '紧急手机号',
            'wx_number' => '微信号',
            'email' => '邮箱',
            'nickname' => '昵称',
            'realname' => '姓名',
            'status' => '状态',
            'occupation' => '职业',
            'province_id' => '省',
            'city_id' => '市',
            'county_id' => '县/区',
            'community' => '社区',
            'address' => '具体地址',
            'lat' => '纬度',
            'lng' => '经度',
            'is_customer' => '是否注册用户',
            'is_popularizer' => '是否推广团长',
            'is_delivery' => '是否配送团长',
            'is_alliance'=>'是否异业联盟商户',
            'head_img_url'=> '头像',
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
