<?php

namespace backend\models\searches;

use common\models\DeliveryType;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DeliveryTypeSearch represents the model behind the search form about `common\models\DeliveryType`.
 */
class DeliveryTypeSearch extends DeliveryType
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'delivery_id', 'delivery_type', 'status', 'company_id'], 'integer'],
            [['params', 'created_at', 'updated_at'], 'safe'],
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
        $query = DeliveryType::find();

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
            'delivery_id' => $this->delivery_id,
            'delivery_type' => $this->delivery_type,
            'status' => $this->status,
            'company_id' => $this->company_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'params', $this->params]);

        return $dataProvider;
    }
}
