<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%user_info}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $phone
 * @property string $em_phone
 * @property string $wx_number
 * @property string $email
 * @property string $nickname
 * @property string $realname
 * @property integer $status
 * @property string $occupation
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $community
 * @property string $address
 * @property double $lat
 * @property double $lng
 * @property integer $is_customer
 * @property integer $is_popularizer
 * @property integer $is_delivery
 * @property integer $is_alliance
 * @property string $head_img_url
 */
class UserInfo extends \yii\db\ActiveRecord
{
    public $province_text;
    public $city_text;
    public $county_text;
    public $customer_id;

    const ROLE_REGISTER = 1;
    const ROLE_UN_REGISTER = 0;

    public static $roleRegisterArr=[
        self::ROLE_REGISTER=>'已注册',
        self::ROLE_UN_REGISTER=>'未注册',
    ];

    public static $roleRegisterCssArr=[
        self::ROLE_REGISTER=>'label label-success',
        self::ROLE_UN_REGISTER=>'label label-warning',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['status', 'province_id', 'city_id', 'county_id', 'is_customer', 'is_popularizer', 'is_delivery','is_alliance'], 'integer'],
            [['lat', 'lng'], 'number'],
            [['phone', 'em_phone', 'wx_number', 'email', 'nickname', 'realname', 'occupation', 'community', 'address','head_img_url'], 'string', 'max' => 255],
            ['phone','unique'],
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


    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                if ($this->is_customer==null){
                    $this->is_customer = CommonStatus::STATUS_DISABLED;
                }
                if ($this->is_popularizer==null){
                    $this->is_popularizer = CommonStatus::STATUS_DISABLED;
                }
                if ($this->is_delivery==null){
                    $this->is_delivery = CommonStatus::STATUS_DISABLED;
                }
                if ($this->is_alliance==null){
                    $this->is_alliance = CommonStatus::STATUS_DISABLED;
                }
            }
            return true;
        } else {
            return false;
        }
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
