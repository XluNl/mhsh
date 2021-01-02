<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_invitation_level}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $customer_id
 * @property integer $one_level_num
 * @property integer $two_level_num
 * @property integer $level
 */
class CustomerInvitationLevel extends \yii\db\ActiveRecord
{
    const LEVEL_NORMAL = 1;
    const LEVEL_ONE = 2;
    const LEVEL_TWO = 3;

    public static $levelTextArr = [
        self::LEVEL_NORMAL=>'初级会员',
        self::LEVEL_ONE=>'VIP会员',
        self::LEVEL_TWO=>'超级会员',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_invitation_level}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['customer_id', 'one_level_num', 'two_level_num'], 'required'],
            [['customer_id', 'one_level_num', 'two_level_num','level'], 'integer'],
            [['customer_id'], 'unique'],
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
            'customer_id' => '被邀请者',
            'one_level_num' => '一级邀请人个数',
            'two_level_num' => '二级邀请人个数',
            'level'=>'等级',
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
