<?php

namespace common\models;

/**
 * This is the model class for table "{{%payment}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $describe
 * @property integer $type
 * @property integer $status
 * @property integer $company_id
 * @property string $created_at
 * @property string $updated_at
 */
class Payment extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
    public static $payStatusArr = array(
       self::STATUS_DISABLE=>'停用',
        self::STATUS_ACTIVE=>'启用'
    );

    const TYPE_UNKNOWN = 0;
    const TYPE_WECHAT = 1;
    const TYPE_BALANCE = 2;
    public static $typeArr = array(
        self::TYPE_UNKNOWN=>'未知',
        self::TYPE_WECHAT=>'微信支付',
        self::TYPE_BALANCE=>'余额支付',
    );

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'created_at'], 'required'],
            [['describe'], 'string'],
            [['type', 'status', 'company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '支付名称',
            'describe' => '支付说明',
            'type' => '1微信支付',
            'status' => '支付方式状态',
            'company_id' => 'Company ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}
