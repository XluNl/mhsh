<?php


namespace backend\models\forms;


use common\models\Common;
use yii\base\Model;

/**
 *  WithdrawForm
 *
 * @property double $available_amount
 * @property double $withdraw_amount
 */
class WithdrawForm extends Model
{
    public $available_amount;
    public $withdraw_amount;

    public function rules()
    {
        return [
            [['withdraw_amount','available_amount'],'required'],
            [['withdraw_amount','available_amount'],'number'],
            ['withdraw_amount','compare','compareValue' => 0.01,'operator' =>'>=',"message" => '提现金额最少为0.01元'],
            ['withdraw_amount','compare','compareAttribute' => 'available_amount','operator' =>'<=',"message" => '提现金额不能大于可提现金额'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'available_amount'=>'可提现金额（单位：元）',
            'withdraw_amount'=>'提现金额（单位：元）',
        ];
    }

    public function storeForm(){
        $this->withdraw_amount = Common::setAmount($this->withdraw_amount);
        return $this;
    }

    public function restoreForm(){
        $this->withdraw_amount = Common::showAmount($this->withdraw_amount);
        return $this;
    }
}