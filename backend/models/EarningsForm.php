<?php

namespace backend\models;

use yii\base\Model;

/**
 *
 * @property string $earnings_amount
 * @property string $remark
 * @property string $restaurant_id
 */
class EarningsForm extends Model
{
    public $earnings_amount;
    public $remark;
    public $restaurant_id;
    public function rules()
    {
        return [
            array('earnings_amount', 'required',"message"=>"充值金额不能为空"),
            array('restaurant_id', 'required',"message"=>"商户ID不能为空"),
            array(array('earnings_amount','restaurant_id'), 'number','message'=>'必须为数字'),
            ['earnings_amount', 'compare', 'compareValue' => 0, 'operator' => '>','on'=>['add']],
            ['earnings_amount', 'compare', 'compareValue' => 50000, 'operator' => '<='],
            [['remark'], 'string', 'max' => 255,"tooLong"=>"最多255个字符"],
            array('remark', 'required',"message"=>"校正备注不能为空",'on'=>['fix']),
        ];
    }
    public function attributeLabels(){
        return array(
            'earnings_amount'  => '充值金额',
            'remark'  => '备注',
        );
    }
    
}
