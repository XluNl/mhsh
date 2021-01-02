<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%storage_bind}}".
 *
 * @property int $id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int $company_id
 * @property int|null $storage_id 仓库id
 * @property string $storage_name 仓库名称
 * @property int $operator_id 操作人id
 * @property string $operator_name 操作人名称
 */
class StorageBind extends \yii\db\ActiveRecord
{
    public $storageArr = [];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%storage_bind}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'storage_id','operator_id'], 'integer'],
            [['storage_name','operator_name'], 'string', 'max' => 255],
            [['storage_id'], 'unique', 'targetAttribute' => ['company_id', 'storage_id'],'message'=>'请勿重复绑定'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'storage_id' => '仓库',
            'storage_name' => '仓库名称',
            'operator_id' => '操作人id',
            'operator_name' => '操作人名称',
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
