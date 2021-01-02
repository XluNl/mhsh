<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%coupon_log}}".
 *
 * @property string $id
 * @property integer $batch_id
 * @property integer $customer_id
 * @property integer $num
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $operator_type
 */
class CouponBatchDrawLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%coupon_batch_draw_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_id', 'customer_id'], 'required'],
            [['batch_id', 'customer_id', 'company_id','num','operator_type','operator_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['remark','operator_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_id' => '批次ID',
            'customer_id' => '客户ID',
            'num' => '数量',
            'remark' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => '公司ID',
            'operator_id'=>'操作人id',
            'operator_name'=>'操作人姓名',
            'operator_type'=>'操作人类型',
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
