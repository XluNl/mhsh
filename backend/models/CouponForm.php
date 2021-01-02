<?php

namespace backend\models;

use yii\base\Model;

/**
 *
 * @property string $startup
 * @property string $discount
 * @property string $start_time
 * @property string $end_time
 * @property string $name
 * @property string $restaurant_id
 */
class CouponForm extends Model
{
    const ALL_IDS = -1;
    public $startup;
    public $discount;
    public $start_time;
    public $end_time;
    public $name;
    public $ids;
    public $sort;
    public $remark;
    public function rules()
    {
        return [
            [['startup','discount','start_time','end_time','name','ids','sort'],'required'],
            [['discount','startup'], 'number','message'=>'必须为数字'],
            //['discount', 'compare', 'compareAttribute' => 'startup', 'operator' => '<='],
            ['end_time', 'compare', 'compareAttribute' => 'start_time', 'operator' => '>='],
            //array(array('start_time','end_time'), 'date','message'=>'必须为时间格式'),
            [['name'], 'string', 'max' => 20],
            [['remark'], 'string', 'max' => 255]
        ];
    }
    public function attributeLabels(){
        return array(
            'startup'  => '满金额',
            'discount'  => '减金额',
            'start_time'  => '有效期开始时间',
            'end_time'  => '有效期结束时间',
            'name'  => '优惠名称',
            'ids'  => '编号',
            'remark'  => '备注',
            'sort'=>'限制类别'
        );
    }
    
}
