<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $nickname
 * @property string $realname
 * @property string $phone
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $county_id
 * @property string $address
 * @property integer $status
 * @property double $lat
 * @property double $lng
 * @property integer $user_id
 * @property-read mixed $user
 * @property string $invite_code
 */
class Customer extends ActiveRecord
{

    public $province_text;
    public $city_text;
    public $county_text;

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;
    public static $StatusArr=[
        self::STATUS_ACTIVE => '启用',
        self::STATUS_DISABLED => '禁用',
    ];

    public static $StatusCssArr=[
        self::STATUS_ACTIVE => 'label label-success',
        self::STATUS_DISABLED => 'label label-danger',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [[ 'province_id', 'city_id', 'county_id', 'status', 'user_id'], 'integer'],
            [['lat', 'lng'], 'number'],
            [['nickname', 'realname', 'address'], 'string', 'max' => 255],
            [['phone','invite_code'], 'string', 'max' => 20],
            ['invite_code','unique'],
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
            'updated_at' => '更新时间',
            'nickname' => '昵称',
            'realname' => '姓名',
            'phone' => '电话',
            'province_id' => '省份',
            'city_id' => '城市',
            'county_id' => '县/区',
            'address' => '地址',
            'status' => '状态',
            'lat' => '纬度',
            'lng' => '经度',
            'user_id' => 'User ID',
            'invite_code'=>'邀请码',
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

    public function createInviteCode() {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d')
            .substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $d = '',
            $f = 0;
            $f < 6;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return $d;
    }

    public function getUser(){
        return $this->hasOne(UserInfo::className(),['id' => 'user_id']);
    }
}
