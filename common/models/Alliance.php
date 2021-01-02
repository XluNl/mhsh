<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%alliance}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property string $head_img_url
 * @property string $nickname
 * @property string $realname
 * @property string $phone
 * @property string $em_phone
 * @property string $wx_number
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $community
 * @property string $address
 * @property double $lng
 * @property double $lat
 * @property integer $status
 * @property string $store_images
 * @property string $qualification_images
 * @property integer $type
 * @property string $business_start
 * @property string $business_end
 * @property integer $auth
 * @property integer $auth_id
 * @property string $contract_images
 */
class Alliance extends \yii\db\ActiveRecord
{

    public $province_text;
    public $city_text;
    public $county_text;

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



    const STATUS_PREPARE = 1;
    const STATUS_ONLINE = 2;
    const STATUS_PENDING = 3;
    const STATUS_OFFLINE = 4;

    public static $statusArr=[
        self::STATUS_PREPARE=>'准备开业',
        self::STATUS_ONLINE=>'营业中',
        self::STATUS_PENDING=>'暂停营业',
        self::STATUS_OFFLINE=>'关店',
    ];

    public static $statusCssArr=[
        self::STATUS_PREPARE=>'label label-info',
        self::STATUS_ONLINE=>'label label-success',
        self::STATUS_PENDING=>'label label-warning',
        self::STATUS_OFFLINE=>'label label-danger',
    ];

    const TYPE_NORMAL = 1;
    public static $typeArr=[
        self::TYPE_NORMAL=>'普通',
    ];

    public static $typeCssArr=[
        self::TYPE_NORMAL=>'label label-info',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%alliance}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'nickname', 'realname', 'phone', 'province_id', 'city_id', 'county_id', 'community', 'address', 'lng', 'lat','business_end','business_start','auth'], 'required'],
            [['user_id', 'company_id', 'province_id', 'city_id', 'county_id','status','type','auth','auth_id'], 'integer'],
            [['created_at', 'updated_at','business_end','business_start'], 'safe'],
            [['lng', 'lat'], 'number'],
            [['store_images','contract_images','qualification_images'], 'string'],
            [['head_img_url', 'nickname', 'realname', 'phone', 'em_phone', 'wx_number', 'community', 'address'], 'string', 'max' => 255],
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
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'head_img_url' => '用户头像',
            'nickname' => '店铺名称',
            'realname' => '姓名',
            'phone' => '手机号',
            'em_phone' => '紧急手机号',
            'wx_number' => '微信号',
            'province_id' => '省份',
            'city_id' => '城市',
            'county_id' => '县/区',
            'community' => '小区',
            'address' => '用户地址',
            'lng' => '经度',
            'lat' => '纬度',
            'status' => '状态值',
            'store_images' => '门店照片',
            'qualification_images' => '资质照片',
            'type'=>'商户类型',
            'business_start'=>'营业开始时间',
            'business_end'=>'营业结束时间',
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
}
