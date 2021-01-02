<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%route}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property string $nickname
 * @property string $realname
 * @property string $phone
 * @property string $em_phone
 * @property integer $status
 */
class Route extends \yii\db\ActiveRecord
{

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;

    public static $statusArr = [
        self::STATUS_DELETED =>'已删除',
        self::STATUS_ACTIVE =>'已启用',
        self::STATUS_DISABLED =>'已禁用',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%route}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nickname', 'realname', 'phone', 'status'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'status'], 'integer'],
            [['nickname', 'realname', 'phone', 'em_phone'], 'string', 'max' => 255],
            ['status','default','value' => 11]
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
            'company_id' => 'Company ID',
            'nickname' => '昵称',
            'realname' => '姓名',
            'phone' => '手机号',
            'em_phone' => '紧急手机号',
            'status' => '状态值',
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
