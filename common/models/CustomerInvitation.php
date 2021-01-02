<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_invitation}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $customer_id
 * @property integer $parent_id
 * @property integer $is_connect
 * @property integer $status
 */
class CustomerInvitation extends \yii\db\ActiveRecord
{

    const IS_CONNECT_TRUE = 1;
    const IS_CONNECT_FALSE = 0;

    public $isConnectArr=[
        self::IS_CONNECT_TRUE=>'已连接',
        self::IS_CONNECT_FALSE=>'已断连',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_invitation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['customer_id', 'parent_id'], 'required'],
            [['customer_id', 'parent_id', 'status','is_connect'], 'integer'],
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
            'parent_id' => '邀请者',
            'is_connect'=>'是否连着',
            'status' => '状态',
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
