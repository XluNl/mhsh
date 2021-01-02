<?php

namespace common\models;

/**
 * This is the model class for table "{{%push_payment_type}}".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $customer_id
 */
class PushPaymentType extends \yii\db\ActiveRecord
{
    const TYPE_DAY = 1;
    const TYPE_WEEK = 2;
    const TYPE_MONTH = 3;
    public static $type_arr = array(
        self::TYPE_DAY=>'日结',
        self::TYPE_WEEK=>'周结',
        self::TYPE_MONTH=>'月结',
    ) ;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%push_payment_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'customer_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '结算方式',
            'customer_id' => '用户ID',
        ];
    }
}
