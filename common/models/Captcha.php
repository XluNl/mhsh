<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%captcha}}".
 *
 * @property integer $id
 * @property string $data
 * @property string $code
 * @property integer $sort
 * @property integer $status
 * @property integer $recode
 * @property string $remark
 * @property string $ip
 * @property integer $fail_num
 * @property string $created_at
 * @property string $updated_at
 */

class Captcha extends ActiveRecord {

    const SORT_SMS_CUSTOMER = 1;
    const SORT_SMS_BUSINESS = 2;
    const SORT_SMS_ALLIANCE = 3;
    public static $sortArr = [
        self::SORT_SMS_CUSTOMER => '用户注册',
        self::SORT_SMS_BUSINESS =>'商户注册',
        self::SORT_SMS_ALLIANCE =>'异业联盟商户注册',
    ];
    const STATUS_UNUSED = 0;
    const STATUS_VALID = 1;
    const STATUS_USED = 2;

	public static $statusArr = [
        self::STATUS_UNUSED => '失效',
        self::STATUS_VALID => '有效',
        self::STATUS_USED => '已验证',
    ];
	
	public static function tableName() {
		return '{{%captcha}}';
	}

    public function rules()
    {
        return [
            [['status', 'recode', 'fail_num','sort'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['data', 'remark'], 'string', 'max' => 30],
            [['code'], 'string', 'max' => 50],
            [['ip'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data' => '手机或者邮箱',
            'code' => '验证码',
            'sort' => '验证码类别',
            'status' => '验证状态',
            'recode' => '验证码发送状态',
            'remark' => '备注',
            'ip' => 'IP地址',
            'fail_num' => '错误次数',
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
}