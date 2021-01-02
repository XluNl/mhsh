<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%bonus_batch_draw_log}}".
 *
 * @property string $id
 * @property integer $batch_id
 * @property integer $draw_type
 * @property integer $draw_type_id
 * @property integer $num
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $operator_type
 * @property integer $biz_type
 * @property integer $biz_id
 */
class BonusBatchDrawLog extends \yii\db\ActiveRecord
{
    public $biz_name;
    const DRAW_TYPE_MANUAL_DRAW = 1;
    const DRAW_TYPE_INVITATION_ACTIVITY = 2;

    public static $drawTypeArr = [
        self::DRAW_TYPE_MANUAL_DRAW=>'手动发放',
        self::DRAW_TYPE_INVITATION_ACTIVITY=>'邀请活动',
    ];

    public static $drawTypeCssArr = [
        self::DRAW_TYPE_MANUAL_DRAW=>'label label-warning',
        self::DRAW_TYPE_INVITATION_ACTIVITY=>'label label-success',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_batch_draw_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_id', 'draw_type', 'biz_type', 'biz_id'], 'required'],
            [['batch_id', 'draw_type', 'draw_type_id', 'num', 'operator_id', 'operator_type', 'biz_id','biz_type'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['remark', 'operator_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_id' => '批次ID',
            'draw_type' => '领取类型',
            'draw_type_id' => '领取类型id',
            'num' => '数量',
            'remark' => '备注',
            'created_at' => '发放时间',
            'updated_at' => '更新时间',
            'operator_id' => '操作人id',
            'operator_name' => '操作人',
            'operator_type' => '操作人类型',
            'biz_type' => '账户类型',
            'biz_id' => '账户ID',
            'biz_name'=>'账户名称',
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
