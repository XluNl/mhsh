<?php

namespace common\models;

/**
 * This is the model class for table "{{%order_refund_apply}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $refund_type
 * @property integer $customer_id
 * @property string $order_no
 * @property integer $sku_id
 * @property string $remark
 * @property integer $result
 * @property integer $operation_id
 * @property string $operation_name
 */
class OrderRefundApply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_refund_apply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'refund_type', 'customer_id', 'order_no'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'refund_type', 'customer_id', 'sku_id', 'result', 'operation_id'], 'integer'],
            [['order_no', 'operation_name'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 255],
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
            'company_id' => '公司ID',
            'refund_type' => '1订单级别退款 2商品级别退款',
            'customer_id' => '客户ID',
            'order_no' => '订单号',
            'sku_id' => '属性ID',
            'remark' => '申请理由',
            'result' => '0待审核 1已同意 2已拒绝',
            'operation_id' => '操作员ID',
            'operation_name' => '操作员',
        ];
    }
}
