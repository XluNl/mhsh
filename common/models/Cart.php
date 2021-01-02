<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%cart}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $user_id
 * @property integer $schedule_id
 * @property integer $num
 * @property integer $is_check
 */
class Cart extends \yii\db\ActiveRecord
{
    const CHECK = 1;
    const UNCHECK = 0;

    public static $checkList=[
        self::CHECK=>'选中',
        self::UNCHECK=>'不选中',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cart}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'user_id', 'schedule_id', 'num'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id', 'schedule_id', 'num','is_check'], 'integer'],
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
            'schedule_id' => '排期ID',
            'num' => '数量',
            'is_check'=> '是否已勾选',
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
