<?php


namespace backend\models\forms;
use common\models\GoodsConstantEnum;
use yii\base\Model;

class DownloadQueryForm extends Model
{
    public $sorting_date;

    public $biz_date;

    public $expect_arrive_time;

    public $order_time_between;

    public $order_time_start;

    public $order_time_end;

    public $delivery_select;

    public $order_owner = GoodsConstantEnum::OWNER_SELF;

    public $big_sort;

    public function rules()
    {
        return [
            [['sorting_date','biz_date'],'required'],
            [['order_time_between','order_time_start','order_time_end','order_owner','big_sort','expect_arrive_time'],'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'biz_date' => '业务  时间',
            'sorting_date' => '分拣时间',
            'order_time_between'=>'订单时间区间',
            'order_time_start'=>'订单时间启',
            'order_time_end'=>'订单时间终',
            'delivery_select'=>'配送团长',
            'order_owner'=>'订单归属',
            'big_sort'=>'商品分类',
            'expect_arrive_time'=>'预计送达时间',
        ];
    }
}