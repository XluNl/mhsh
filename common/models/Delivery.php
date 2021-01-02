<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%delivery}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $nickname
 * @property string $realname
 * @property string $phone
 * @property string $em_phone
 * @property integer $wx_number
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $community
 * @property string $address
 * @property double $lng
 * @property double $lat
 * @property integer $status
 * @property integer $company_id
 * @property integer $min_amount_limit
 * @property integer $allow_order
 * @property integer $type
 * @property integer $user_id
 * @property string $head_img_url
 * @property integer $auth
 * @property integer $auth_id
 * @property string $contract_images
 */
class Delivery extends ActiveRecord
{
    public $province_text;
    public $city_text;
    public $county_text;
    public $user_count; // 合伙人粉丝数

    const ALLOW_ORDER_TRUE = 1;
    const ALLOW_ORDER_FALSE = 0;

    public static $allowOrderArr=[
        self::ALLOW_ORDER_TRUE=>'允许下单',
        self::ALLOW_ORDER_FALSE=>'不允许下单',
    ];

    public static $allowOrderCssArr=[
        self::ALLOW_ORDER_TRUE=>'label label-success',
        self::ALLOW_ORDER_FALSE=>'label label-danger',
    ];

    const TYPE_COOPERATE = 1;
    const TYPE_DIRECT = 2;

    public static $typeArr=[
        self::TYPE_COOPERATE=>'合作',
        self::TYPE_DIRECT=>'直营',
    ];
    public static $typeCssArr=[
        self::TYPE_COOPERATE=>'label label-success',
        self::TYPE_DIRECT=>'label label-info',
    ];


    public static $typeLenArr=[
        self::TYPE_COOPERATE=>100000,
        self::TYPE_DIRECT=>90000000,
    ];

    const AUTH_STATUS_NO_AUTH = 1;
    const AUTH_STATUS_AUTH = 2;
    const AUTH_STATUS_CANCEL_AUTH = 3;

    public static $authStatusArr=[
        self::AUTH_STATUS_NO_AUTH=>'未认证',
        self::AUTH_STATUS_AUTH=>'已认证',
        self::AUTH_STATUS_CANCEL_AUTH=>'取消认证',
    ];

    public static $authStatusCssArr=[
        self::AUTH_STATUS_NO_AUTH=>'label label-info',
        self::AUTH_STATUS_AUTH=>'label label-success',
        self::AUTH_STATUS_CANCEL_AUTH=>'label label-warning',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nickname', 'realname', 'phone', 'province_id', 'city_id', 'county_id', 'community', 'address', 'lng', 'lat', 'status','type'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['province_id', 'city_id', 'county_id', 'status', 'company_id',  'min_amount_limit', 'allow_order','type','user_id','auth','auth_id'], 'integer'],
            [['lng', 'lat'], 'number'],
            [['nickname', 'realname', 'address','head_img_url'], 'string', 'max' => 255],
            [['phone','em_phone'], 'string', 'max' => 20],
            [['community','wx_number'], 'string', 'max' => 60],
            ['contract_images','string','max'=>4095]
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
            'nickname' => '昵称',
            'realname' => '姓名',
            'phone' => '联系电话',
            'em_phone'=>'紧急手机号',
            'wx_number'=>'微信号',
            'province_id' => '省份',
            'city_id' => '城市',
            'county_id' => '县/区',
            'community' => '小区',
            'address' => '用户地址',
            'lng' => '经度',
            'lat' => '纬度',
            'status' => '状态值',
            'company_id' => 'Company ID',
            'min_amount_limit' => '起送金额',
            'allow_order' => '是否允许接收订单',
            'type'=>'配送点类型',
            'user_id'=>'用户ID',
            'head_img_url'=>'用户头像',
            'auth'=>'认证状态',
            'auth_id'=>'认证id',
            'contract_images'=>'合同照片',
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

    public function getUserinfo() {
        return $this->hasOne(UserInfo::className(), ['id' => 'user_id']);
    }
}
