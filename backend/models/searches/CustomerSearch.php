<?php

namespace backend\models\searches;

use common\models\Customer;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CustomerSearch represents the model behind the search form about `common\models\Customer`.
 */
class CustomerSearch extends Customer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id',  'province_id', 'city_id', 'county_id', 'status', 'user_id'], 'integer'],
            [['created_at', 'updated_at', 'nickname', 'realname', 'phone', 'address'], 'safe'],
            [['lat', 'lng'], 'number'],
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
        $query = Customer::find();

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
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'county_id' => $this->county_id,
            'status' => $this->status,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'realname', $this->realname])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'address', $this->address]);

        return $dataProvider;
    }
}
