<?php


namespace backend\models\forms;


use common\models\GoodsConstantEnum;
use yii\base\Model;

/**
 *  GoodsDeliveryForm
 *
 * @property integer $delivery_id
 * @property string $goods_owner
 * @property string $goods_ids
 */
class GoodsDeliveryForm extends Model
{
    public $delivery_id;
    public $goods_owner = GoodsConstantEnum::OWNER_SELF;
    public $goods_ids = [];

    public function rules()
    {
        return [
            [['delivery_id','goods_ids'],'required'],
            [['delivery_id'],'integer'],
            [['goods_ids'],'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'delivery_id' => '配送团长',
            'goods_owner'=>'归属',
            'goods_ids' => '商品',
        ];
    }
}