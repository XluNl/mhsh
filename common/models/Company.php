<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%company}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $address
 * @property string $contact
 * @property string $office_phone
 * @property string $telphone
 * @property string $fax
 * @property string $zip_code
 * @property integer $status
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $email
 * @property string $service_phone
 */
class Company extends \yii\db\ActiveRecord
{
    public static $type_arr_label=[
        '0'=>'label label-danger',
        '1'=>'label label-success',
    ];
    public static $type_arr=[
        '0'=>'停用',
        '1'=>'启用',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'address', 'contact', 'province_id', 'telphone', 'city_id', 'county_id', 'email','service_phone'], 'required'],
            [['name', 'address', 'contact', 'office_phone', 'telphone', 'fax', 'zip_code', 'email','service_phone'], 'string'],
            [['status','province_id','city_id','county_id'], 'integer'],
            [['updated_at','created_at'], 'safe'],
            [['email'],'email'],
            [['name', 'contact','email'], 'string', 'length' => [0, 24]],
            [['telphone'],'match','pattern'=>'/^[1][356879][0-9]{9}$/','message'=>'手机号格式不正确'],
            [['office_phone', 'fax'],'match','pattern'=>'/^([0-9]{7,8}|400[0-9]{7}|800[0-9]{7}|[0-9]{3,4}-[0-9]{7,8})$/','message'=>'座机格式不正确（座机格式为88888888或者0555-23232323）'],
            [['zip_code'], 'match','pattern'=>'/^[0-9]{6}$/','message'=>'邮编为6为数字'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '公司名称',
            'address' => '公司地址',
            'contact' => '联系人',
            'office_phone' => '办公室电话',
            'telphone' => '移动电话',
            'fax' => '传真',
            'zip_code' => '邮编',
            'status' => '状态',
            'province_id'=>'省份',
            'city_id'=>'城市',
            'county_id'=>'县/区',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'email' => '电子邮箱',
            'service_phone' => '客服联系电话',
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
