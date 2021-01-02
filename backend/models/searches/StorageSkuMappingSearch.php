<?php

namespace backend\models\searches;

use common\models\StorageSkuMapping;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StorageSkuMappingSearch represents the model behind the search form of `common\models\StorageSkuMapping`.
 */
class StorageSkuMappingSearch extends StorageSkuMapping
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'goods_id', 'sku_id', 'company_id', 'storage_sku_id'], 'integer'],
            [['created_at', 'updated_at','storage_sku_num'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = StorageSkuMapping::find();

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
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'goods_id' => $this->goods_id,
            'sku_id' => $this->sku_id,
            'company_id' => $this->company_id,
            'storage_sku_id' => $this->storage_sku_id,
            'storage_sku_num' => $this->storage_sku_num,
        ]);

        return $dataProvider;
    }
}
