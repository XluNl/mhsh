<?php

namespace common\models;

/**
 * This is the model class for table "{{%auth_assignment}}".
 *
 * @property string $item_name
 * @property string $user_id
 * @property integer $created_at
 * @property integer $company_id
 *
 * @property AuthItem $itemName
 */
class AuthAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_assignment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['created_at', 'company_id'], 'integer'],
            //[['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => Item::className(), 'targetAttribute' => ['item_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Item Name',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'company_id' => 'Company ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
//     public function getItemName()
//     {
//         return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
//     }
}
