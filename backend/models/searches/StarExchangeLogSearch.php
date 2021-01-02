<?php

namespace backend\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\StarExchangeLog;

/**
 * StarExchangeLogSearch represents the model behind the search form of `common\models\StarExchangeLog`.
 */
class StarExchangeLogSearch extends StarExchangeLog
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'amount', 'biz_type', 'biz_id', 'balance_id', 'balance_log_id'], 'integer'],
            [['created_at', 'updated_at', 'trade_no', 'exchange_time', 'phone'], 'safe'],
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
        $query = StarExchangeLog::find();

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
            'exchange_time' => $this->exchange_time,
            'amount' => $this->amount,
            'biz_type' => $this->biz_type,
            'biz_id' => $this->biz_id,
            'balance_id' => $this->balance_id,
            'balance_log_id' => $this->balance_log_id,
        ]);

        $query->andFilterWhere(['like', 'trade_no', $this->trade_no])
            ->andFilterWhere(['like', 'phone', $this->phone]);

        return $dataProvider;
    }
}
