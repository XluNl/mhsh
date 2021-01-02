<?php

namespace backend\models\searches;

use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GoodsSearch represents the model behind the search form about `common\models\Goods`.
 */
class GoodsSearch extends Goods
{
    public $ownerTypeOptions = [];
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id',  'goods_status', 'display_order', 'supplier_id', 'goods_sold_channel_type', 'goods_type', 'goods_owner', 'goods_owner_id', 'goods_cart', 'company_id'], 'integer'],
            [['goods_name', 'goods_img', 'goods_describe', 'created_at','sort_1', 'sort_2', 'updated_at','goods_images', 'goods_video'], 'safe'],
            ['goods_owner', 'default', 'value' => null],
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
        $goodsTable = Goods::tableName();
        $query = Goods::find();

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

        if (StringUtils::isBlank($this->goods_owner)){
            $this->goods_owner = GoodsConstantEnum::OWNER_SELF;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'sort_1' => $this->sort_1,
            'sort_2' => $this->sort_2,
            'goods_status' => $this->goods_status,
            'display_order' => $this->display_order,
            'supplier_id' => $this->supplier_id,
            'goods_sold_channel_type' => $this->goods_sold_channel_type,
            'goods_type' => $this->goods_type,
            'goods_owner' => $this->goods_owner,
            'goods_owner_id' => $this->goods_owner_id,
            'goods_cart' => $this->goods_cart,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            "{$goodsTable}.company_id" => $this->company_id,
        ]);
        $query->andFilterWhere(['goods_status'=>GoodsConstantEnum::$activeStatusArr]);
        $query->andFilterWhere(['like', 'goods_name', trim($this->goods_name)])
            ->andFilterWhere(['like', 'goods_img', $this->goods_img])
            ->andFilterWhere(['like', 'goods_describe', $this->goods_describe])
            ->andFilterWhere(['like', 'goods_images', $this->goods_images])
            ->andFilterWhere(['like', 'goods_video', $this->goods_video]);


        $query->with(['goodsSku'=>function(){
            return $this->hasMany(GoodsSku::className(),['goods_id' => 'id'])
                ->where(['sku_status'=>GoodsConstantEnum::$activeStatusArr])
                ->with("storageSkuMapping");
        }]);

        $query->orderBy('id desc');
        return $dataProvider;
    }
}
