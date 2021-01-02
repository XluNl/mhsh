<?php

namespace frontend\models;

use yii\base\Model;

/**
 *
 * @property integer $amount
 */
class RechargeForm extends Model
{
    public $amount;
    public function rules()
    {
        return [
            [['amount'], 'required'],
            ['amount', 'number'],
            ['amount', 'default', 'value' => '1'],
            ['amount', 'compare', 'compareValue' => 1, 'operator' => '>=',"message"=>"充值金额必须大于1元"],
        ];
    }
    
    public function attributeLabels(){
        return array(
            'amount'  => '充值金额',
        );
    }
    
}
