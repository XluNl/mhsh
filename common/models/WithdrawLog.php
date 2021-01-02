<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%withdraw_log}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $withdraw_id
 * @property integer $action
 * @property integer $role
 * @property integer $operator_id
 * @property string $operator_name
 * @property string $remark
 */
class WithdrawLog extends \yii\db\ActiveRecord
{
    const ACTION_OWNER_APPLY = 1;
    const ACTION_ADMIN_ACCEPT = 2;
    const ACTION_ADMIN_DEAL = 3;
    const ACTION_ADMIN_DENY = 4;
    const ACTION_ADMIN_RETRY_WECHAT = 5;
    const ACTION_ADMIN_RETURN = 6;

    public static $actionArr=[
        self::ACTION_OWNER_APPLY=>'所有人申请',
        self::ACTION_ADMIN_ACCEPT=>'管理员审核通过',
        self::ACTION_ADMIN_DEAL=>'管理员处理打款',
        self::ACTION_ADMIN_DENY=>'管理员审核拒绝并退回',
        self::ACTION_ADMIN_RETRY_WECHAT=>'管理员尝试微信打款',
        self::ACTION_ADMIN_RETURN=>'管理员退回余额',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%withdraw_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['withdraw_id', 'action', 'role', 'operator_id', 'operator_name'], 'required'],
            [['withdraw_id', 'action', 'role', 'operator_id'], 'integer'],
            [['operator_name', 'remark'], 'string', 'max' => 255],
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
            'withdraw_id' => '提现ID',
            'action' => '操作',
            'role' => '角色',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人',
            'remark' => '备注',
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
