<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%distribute_balance}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $user_id
 * @property integer $biz_type
 * @property integer $biz_id
 * @property integer $amount
 * @property integer $remain_amount
 * @property integer $version
 */
class DistributeBalance extends \yii\db\ActiveRecord
{

    public $search_name;
    public $search_phone;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%distribute_balance}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'biz_type', 'biz_id', 'amount', 'remain_amount', 'version','user_id'], 'integer'],
            [['biz_type', 'biz_id', 'remain_amount'], 'required'],
            [['amount','remain_amount'],'default','value' => 0]
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
            'updated_at' => '修改时间',
            'company_id' => 'Company ID',
            'user_id'=> '用户ID',
            'biz_type' => '账户类型',
            'biz_id' => '业务ID',
            'amount' => '余额',
            'remain_amount' => '待入账金额',
            'version' => '版本号',
            'search_name'=>'名称',
            'search_phone'=>'手机号',
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
