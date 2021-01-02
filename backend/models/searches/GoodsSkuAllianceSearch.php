<?php

namespace backend\models\searches;

use common\models\Alliance;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuAlliance;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GoodsSkuAllianceSearch represents the model behind the search form about `common\models\GoodsSkuAlliance`.
 */
class GoodsSkuAllianceSearch extends GoodsSkuAlliance
{
    public $ownerTypeOptions = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'goods_id', 'goods_owner_id', 'sku_id', 'sku_stock', 'display_order', 'purchase_price', 'reference_price', 'company_id', 'audit_status', 'operator_id','goods_owner_type','display_channel','sort_1','sort_2'], 'integer'],
            [['goods_name', 'goods_img', 'goods_detail', 'goods_type', 'sku_name', 'sku_img', 'sku_unit', 'sku_describe', 'sku_status', 'features', 'production_date', 'expired_date', 'created_at', 'updated_at', 'audit_result', 'operator_name','nickname','realname','phone'], 'safe'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $allianceTable = Alliance::tableName();
        $deliveryTable = Delivery::tableName();
        $goodsSkuAlliance = GoodsSkuAlliance::tableName();
        $query = GoodsSkuAlliance::find();
        $OWNER_TYPE_SELF = GoodsConstantEnum::OWNER_SELF;
        $OWNER_TYPE_HA = GoodsConstantEnum::OWNER_HA;
        $OWNER_TYPE_DELIVERY = GoodsConstantEnum::OWNER_DELIVERY;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            "{$goodsSkuAlliance}.id" => $this->id,
            "{$goodsSkuAlliance}.goods_id" => $this->goods_id,
            "{$goodsSkuAlliance}.sort_1" => $this->sort_1,
            "{$goodsSkuAlliance}.sort_2" => $this->sort_2,
            "{$goodsSkuAlliance}.display_channel" => $this->display_channel,
            "{$goodsSkuAlliance}.goods_owner_type" => $this->goods_owner_type,
            "{$goodsSkuAlliance}.goods_owner_id" => $this->goods_owner_id,
            "{$goodsSkuAlliance}.sku_id" => $this->sku_id,
            "{$goodsSkuAlliance}.sku_stock" => $this->sku_stock,
            "{$goodsSkuAlliance}.display_order" => $this->display_order,
            "{$goodsSkuAlliance}.purchase_price" => $this->purchase_price,
            "{$goodsSkuAlliance}.reference_price" => $this->reference_price,
            "{$goodsSkuAlliance}.production_date" => $this->production_date,
            "{$goodsSkuAlliance}.expired_date" => $this->expired_date,
            "{$goodsSkuAlliance}.created_at" => $this->created_at,
            "{$goodsSkuAlliance}.updated_at" => $this->updated_at,
            "{$goodsSkuAlliance}.company_id" => $this->company_id,
            "{$goodsSkuAlliance}.audit_status" => $this->audit_status,
            "{$goodsSkuAlliance}.operator_id" => $this->operator_id,
            "{$goodsSkuAlliance}.sku_status" => $this->sku_status,
            "{$goodsSkuAlliance}.goods_type" => $this->goods_type,
        ]);

        $query->andFilterWhere(['like', "{$goodsSkuAlliance}.goods_name", $this->goods_name])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.goods_img", $this->goods_img])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.goods_detail", $this->goods_detail])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.sku_name", $this->sku_name])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.sku_img", $this->sku_img])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.sku_unit", $this->sku_unit])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.sku_describe", $this->sku_describe])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.features", $this->features])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.audit_result", $this->audit_result])
            ->andFilterWhere(['like', "{$goodsSkuAlliance}.operator_name", $this->operator_name]);

        /*if ($OWNER_TYPE_HA==$this->goods_owner_type){
            $query->andFilterWhere(['like', "{$allianceTable}.nickname", $this->nickname])
                ->andFilterWhere(['like', "{$allianceTable}.realname", $this->realname])
                ->andFilterWhere(['like', "{$allianceTable}.phone", $this->phone]);
        }
        else if ($OWNER_TYPE_DELIVERY==$this->goods_owner_type){
            $query->andFilterWhere(['like', "{$deliveryTable}.nickname", $this->nickname])
                ->andFilterWhere(['like', "{$deliveryTable}.realname", $this->realname])
                ->andFilterWhere(['like', "{$deliveryTable}.phone", $this->phone]);
        }
        else{
            $query->andFilterWhere([
                'or',
                [
                    'and',
                    ['like', "{$allianceTable}.nickname", $this->nickname],
                    ['like', "{$allianceTable}.realname", $this->realname],
                    ['like', "{$allianceTable}.phone", $this->phone]
                ],
                [
                    'and',
                    ['like', "{$deliveryTable}.nickname", $this->nickname],
                    ['like', "{$deliveryTable}.realname", $this->realname],
                    ['like', "{$deliveryTable}.phone", $this->phone]
                ],
            ]);
        }*/


        //$query->leftJoin($allianceTable,"{$goodsSkuAlliance}.goods_owner_type = {$OWNER_TYPE_HA} and {$goodsSkuAlliance}.goods_owner_id={$allianceTable}.id");
        //$query->leftJoin($deliveryTable,"{$goodsSkuAlliance}.goods_owner_type = {$OWNER_TYPE_DELIVERY} and {$goodsSkuAlliance}.goods_owner_id={$deliveryTable}.id");
        $query->with(['alliance','delivery']);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
