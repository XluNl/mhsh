<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_invitation_activity_result}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $activity_id
 * @property integer $customer_id
 * @property string $customer_name
 * @property integer $invitation_count
 * @property integer $invitation_order_count
 * @property integer $invitation_children_count
 * @property integer $invitation_children_order_count
 * @property string $customer_phone
 * @property string $prizes
 * @property string $children
 */
class CustomerInvitationActivityResult extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_invitation_activity_result}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['activity_id', 'customer_id', 'invitation_count', 'invitation_order_count','invitation_children_count','invitation_children_order_count'], 'integer'],
            [['customer_id'], 'required'],
            [['prizes', 'children'], 'string'],
            [['customer_name', 'customer_phone'], 'string', 'max' => 255],
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
            'activity_id' => '活动id',
            'customer_id' => '客户id',
            'customer_name' => '客户名称',
            'invitation_count' => '一级邀请人数',
            'invitation_order_count' => '一级邀请下单人数',
            'invitation_children_count' => '二级邀请人数',
            'invitation_children_order_count' => '二级邀请下单人数',
            'customer_phone' => '客户手机号',
            'prizes' => '奖品',
            'children' => '邀请数据',
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
