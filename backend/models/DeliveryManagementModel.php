<?php


namespace backend\models;


use yii\base\Model;

/**
 * @property integer $schedule_id
 * @property string $schedule_name
 * @property integer $goods_id
 * @property string $goods_name
 * @property integer $sku_id
 * @property string $sku_name
 * @property string $sku_unit
 * @property string $expect_arrive_time
 * Class DeliveryManagementModel
 * @package backend\models
 */
class DeliveryManagementModel extends Model
{
    public $schedule_id;
    public $schedule_name;
    public $goods_id;
    public $goods_name;
    public $sku_id;
    public $sku_name;
    public $sku_unit;
    public $expect_arrive_time;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'schedule_id' => '排期id',
            'schedule_name' => '排期名称',
            'goods_id' => '商品id',
            'goods_name' => '商品名称',
            'sku_id' => '属性id',
            'sku_name' => '属性名称',
            'sku_unit' => '属性单位',
            'expect_arrive_time' => '预计送达时间',
        ];
    }

}