<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%withdraw_apply}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $biz_type
 * @property integer $biz_id
 * @property string $biz_name
 * @property integer $audit_status
 * @property string $audit_remark
 * @property integer $amount
 * @property integer $type
 * @property integer $process_status
 * @property integer $version
 * @property integer $is_return
 * @property string $remark
 */
class WithdrawApply extends ActiveRecord
{

    const TYPE_OFFLINE = 1;
    const TYPE_WECHAT = 2;

    public static $typeArr=[
        self::TYPE_OFFLINE=>'线下打款',
        self::TYPE_WECHAT=>'微信提现',
    ];
    public static $typeCssArr=[
        self::TYPE_OFFLINE=>'label label-info',
        self::TYPE_WECHAT=>'label label-primary',
    ];

    const AUDIT_STATUS_APPLY = 1;
    const AUDIT_STATUS_ACCEPT = 2;
    const AUDIT_STATUS_DENY = 3;
    const AUDIT_STATUS_DRAW_BACK = 4;

    public static $auditStatusArr =[
        self::AUDIT_STATUS_APPLY=>'申请中',
        self::AUDIT_STATUS_ACCEPT=>'审核通过',
        self::AUDIT_STATUS_DENY=>'审核驳回',
        self::AUDIT_STATUS_DRAW_BACK=>'主动撤回',
    ];

    public static $auditStatusCssArr =[
        self::AUDIT_STATUS_APPLY=>'label label-info',
        self::AUDIT_STATUS_ACCEPT=>'label label-success',
        self::AUDIT_STATUS_DENY=>'label label-danger',
        self::AUDIT_STATUS_DRAW_BACK=>'label label-warning',
    ];

    const PROCESS_STATUS_UN_DEAL = 0;
    const PROCESS_STATUS_SUCCESS = 1;
    const PROCESS_STATUS_FAILED = 2;

    public static $processStatusArr=[
        self::PROCESS_STATUS_UN_DEAL=>'待处理',
        self::PROCESS_STATUS_SUCCESS=>'处理成功',
        self::PROCESS_STATUS_FAILED=>'处理失败',
    ];

    public static $processStatusCssArr=[
        self::PROCESS_STATUS_UN_DEAL=>'label label-info',
        self::PROCESS_STATUS_SUCCESS=>'label label-success',
        self::PROCESS_STATUS_FAILED=>'label label-danger',
    ];

    const IS_RETURN_FALSE = 0;
    const IS_RETURN_TRUE = 1;

    public static $isReturnArr=[
        self::IS_RETURN_FALSE=>'未退还',
        self::IS_RETURN_TRUE=>'已退还',
    ];

    public static $isReturnCssArr=[
        self::IS_RETURN_FALSE=>'label label-info',
        self::IS_RETURN_TRUE=>'label label-primary',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%withdraw_apply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['biz_type', 'biz_id', 'biz_name'], 'required'],
            [['biz_type', 'biz_id', 'audit_status', 'amount', 'type', 'process_status', 'version', 'is_return'], 'integer'],
            [['biz_name','remark','audit_remark'], 'string', 'max' => 255],
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
            'biz_type' => '业务类型',
            'biz_id' => '业务ID',
            'biz_name' => '业务名称',
            'audit_status' => '审核状态',
            'amount' => '提现金额',
            'type' => '提现方式',
            'process_status' => '处理状态',
            'version' => '版本号',
            'is_return' => '退还状态',
            'remark'=>'备注',
            'audit_remark'=>'审核备注',
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
