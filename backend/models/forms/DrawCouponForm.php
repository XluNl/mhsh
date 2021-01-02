<?php


namespace backend\models\forms;


use yii\base\Model;

/**
 *  DrawCouponForm
 *
 * @property integer $customer_id
 * @property string $batch_no
 * @property integer $num
 * @property string $remark
 */
class DrawCouponForm extends Model
{
    public $customer_id;
    public $batch_no;
    public $num;
    public $remark;

    public function rules()
    {
        return [
            [['batch_no','remark','customer_id','num'],'required'],
            [['batch_no','remark'],'string','max' => 255],
            [['customer_id','num'],'integer'],
            ['num','compare','compareValue' => 0,'operator' =>'>',"message" => '数量最少为1张'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'batch_no' => '优惠批次号',
            'customer_id' => '客户id',
            'num' => '数量',
            'remark'=>'备注',
        ];
    }
}