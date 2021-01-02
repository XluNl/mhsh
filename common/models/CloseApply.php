<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%close_apply}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $biz_type
 * @property integer $biz_id
 * @property integer $name
 * @property integer $phone
 * @property string $images
 * @property string $remark
 * @property integer $status
 * @property integer $action
 * @property integer $operator_id
 * @property string $operator_name
 * @property string $operator_remark
 */
class CloseApply extends \yii\db\ActiveRecord
{

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

    const APPLY_TYPE_HA= BizTypeEnum::BIZ_TYPE_HA;

    public static $applyTypeArr=[
        self::APPLY_TYPE_HA => '异业联盟商家',
    ];
    public static $applyTypeCssArr=[
        self::APPLY_TYPE_HA => 'label label-success',
    ];
    public static $applyTypeName=[
        self::APPLY_TYPE_HA => 'ha',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%close_apply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'biz_type', 'biz_id'], 'required'],
            [['user_id', 'company_id', 'biz_type', 'biz_id', 'status', 'operator_id','action'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['images'], 'string', 'max' => 4096],
            [['remark', 'operator_name','name','phone','operator_remark'], 'string', 'max' => 255],
            [['images','remark'], 'required','on'=>'ha'],

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
            'company_id' => '公司id',
            'biz_type' => '业务类型',
            'biz_id' => '业务id',
            'name' => '业务名称',
            'phone' => '业务手机号',
            'images' => '凭证',
            'remark' => '理由',
            'action'=>'审核状态',
            'status' => '状态值',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人姓名',
            'operator_remark'=>'操作备注',
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
