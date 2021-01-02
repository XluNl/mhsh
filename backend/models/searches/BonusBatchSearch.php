<?php

namespace backend\models\searches;

use common\models\BonusBatch;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BonusBatchSearch represents the model behind the search form about `common\models\BonusBatch`.
 */
class BonusBatchSearch extends BonusBatch
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'type', 'operator_id', 'draw_amount', 'amount', 'version'], 'integer'],
            [['created_at', 'updated_at', 'batch_no', 'name', 'remark', 'draw_start_time', 'draw_end_time', 'operator_name', 'status'], 'safe'],
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
        $query = BonusBatch::find();

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
            'type' => $this->type,
            'draw_start_time' => $this->draw_start_time,
            'draw_end_time' => $this->draw_end_time,
            'operator_id' => $this->operator_id,
            'draw_amount' => $this->draw_amount,
            'amount' => $this->amount,
            'version' => $this->version,
        ]);

        $query->andFilterWhere(['like', 'batch_no', $this->batch_no])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'remark', $this->remark])
            ->andFilterWhere(['like', 'operator_name', $this->operator_name])
            ->andFilterWhere(['like', 'status', $this->status]);
        $query->andFilterWhere(['status'=>array_keys(BonusBatch::$statusDisplayArr)]);
        return $dataProvider;
    }
}
