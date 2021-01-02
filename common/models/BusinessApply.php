<?php

namespace common\models;

use common\utils\StringUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%business_apply}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $user_id
 * @property integer $type
 * @property string $nickname
 * @property string $realname
 * @property string $em_phone
 * @property string $wx_number
 * @property string $occupation
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $community
 * @property string $address
 * @property double $lat
 * @property double $lng
 * @property string $images
 * @property string $remark
 * @property string $invite_code
 * @property integer $has_store
 * @property integer $operator_id
 * @property string $operator_name
 * @property string $operator_remark
 * @property integer $action
 * @property integer $status
 * @property integer $company_id
 * @property string $head_img_url
 * @property string $ext_images
 * @property string $ext_v1
 * @property string $ext_v2
 * @property string $ext_v3
 * @property string $ext_v4
 * @property string $ext_v5
 */
class BusinessApply extends ActiveRecord
{
    public $phone;

    const APPLY_TYPE_POPULARIZER = 1;
    const APPLY_TYPE_DELIVERY = 2;
    const APPLY_TYPE_HA= 3;

    public static $applyTypeArr=[
        self::APPLY_TYPE_POPULARIZER => '推广团长',
        self::APPLY_TYPE_DELIVERY => '配送团长',
        self::APPLY_TYPE_HA => '异业联盟商家',
    ];
    public static $applyTypeCssArr=[
        self::APPLY_TYPE_POPULARIZER => 'label label-info',
        self::APPLY_TYPE_DELIVERY => 'label label-primary',
        self::APPLY_TYPE_HA => 'label label-success',
    ];
    public static $applyTypeName=[
        self::APPLY_TYPE_POPULARIZER => 'popularizer',
        self::APPLY_TYPE_DELIVERY => 'delivery',
        self::APPLY_TYPE_HA => 'ha',
    ];


    const ACTION_APPLY = 1;
    const ACTION_ACCEPT = 2;
    const ACTION_DENY = 3;
    const ACTION_CANCEL = 4;
    const ACTION_DELETED = 5;

    public static $actionArr=[
        self::ACTION_APPLY => '申请中',
        self::ACTION_ACCEPT => '审核通过',
        self::ACTION_DENY => '审核拒绝',
        self::ACTION_CANCEL => '主动撤回',
    ];

    public static $actionCssArr=[
        self::ACTION_APPLY => 'label label-primary',
        self::ACTION_ACCEPT => 'label label-success',
        self::ACTION_DENY => 'label label-danger',
        self::ACTION_CANCEL => 'label label-warning',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%business_apply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at','ext_v1','ext_v2','ext_v3','ext_v4','ext_v5'], 'safe'],
            [['user_id', 'type', 'action'], 'required'],
            [['user_id', 'type', 'province_id', 'city_id', 'county_id', 'has_store', 'operator_id', 'action', 'status','company_id'], 'integer'],
            [['lat', 'lng'], 'number'],
            [['ext_images'],'string'],
            [['nickname','realname',  'em_phone', 'wx_number', 'community', 'address', 'images', 'remark', 'invite_code', 'operator_name', 'operator_remark','occupation','head_img_url'], 'string', 'max' => 255],
            [['company_id','realname', 'province_id', 'city_id', 'county_id', 'community', 'address','lat', 'lng'], 'required','on'=>'popularizer'],
            [['company_id','realname',  'province_id', 'city_id', 'county_id', 'community', 'address','lat', 'lng','has_store'], 'required','on'=>'delivery'],
            [['company_id','nickname','occupation',  'province_id', 'city_id', 'county_id', 'community', 'address','lat', 'lng','images','ext_images'], 'required','on'=>'ha'],
            [['ext_v1','ext_v2'], 'required','on'=>'ha','message' => '营业时间不能为空'],
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
            'user_id' => '用户ID',
            'type' => '申请类型',
            'nickname' => '昵称',
            'realname' => '姓名',
            'em_phone' => '紧急手机号',
            'wx_number' => '微信号',
            'occupation'=> '职业',
            'province_id' => '省',
            'city_id' => '市',
            'county_id' => '县/区',
            'community' => '社区',
            'address' => '具体地址',
            'lat' => '纬度',
            'lng' => '经度',
            'images' => '图片',
            'remark' => '备注',
            'invite_code' => '推荐码',
            'has_store' => '是否有门店',
            'operator_id' => '审核人ID',
            'operator_name' => '审核人名称',
            'operator_remark' => '审核备注',
            'action' => '审核状态',
            'status' => '状态',
            'company_id'=>'公司ID',
            'phone'=>'手机号',
            'head_img_url'=>'头像图片',
            'ext_images'=>'扩展图片',
            'ext_v1'=>'扩展字段1',
            'ext_v2'=>'扩展字段2',
            'ext_v3'=>'扩展字段3',
            'ext_v4'=>'扩展字段4',
            'ext_v5'=>'扩展字段5',
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

    public function beforeSave($insert){
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                if (StringUtils::isBlank($this->action)){
                    $this->action = self::ACTION_APPLY;
                }
                if (StringUtils::isBlank($this->status)){
                    $this->status =  CommonStatus::STATUS_ACTIVE;
                }
            }
            return true;
        }else{
            return false;
        }
    }
}
