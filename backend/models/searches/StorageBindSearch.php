<?php

namespace backend\models\searches;

use common\models\StorageBind;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StorageBindSearch represents the model behind the search form of `common\models\StorageBind`.
 */
class StorageBindSearch extends StorageBind
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'storage_id','operator_id'], 'integer'],
            [['created_at', 'updated_at', 'storage_name','operator_name'], 'safe'],
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
        $query = StorageBind::find();

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
            'company_id' => $this->company_id,
            'storage_id' => $this->storage_id,
            'operator_id' => $this->operator_id,
        ]);

        $query->andFilterWhere(['like', 'storage_name', $this->storage_name])
            ->andFilterWhere(['like', 'operator_name', $this->operator_name]);

        return $dataProvider;
    }
}
