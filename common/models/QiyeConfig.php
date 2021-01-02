<?php

namespace common\models;

/**
 * This is the model class for table "{{%qiye_config}}".
 *
 * @property string $id
 * @property string $corpid
 * @property string $secret
 * @property string $access_token
 * @property integer $expired_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $company_id
 */
class QiyeConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qiye_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['expired_at', 'created_at', 'updated_at', 'company_id'], 'integer'],
            [['corpid', 'secret', 'access_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corpid' => '企业ID',
            'secret' => '秘钥',
            'access_token' => 'token',
            'expired_at' => 'token有效期',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => '公司ID',
        ];
    }
}
