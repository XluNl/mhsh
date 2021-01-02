<?php

namespace backend\models\searches;

use common\models\CustomerInvitationActivityPrize;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CustomerInvitationActivityPrizeSearch represents the model behind the search form about `common\models\CustomerInvitationActivityPrize`.
 */
class CustomerInvitationActivityPrizeSearch extends CustomerInvitationActivityPrize
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'operator_id', 'range_start', 'range_end', 'expect_quantity', 'real_quantity','activity_id', 'num','company_id'], 'integer'],
            [['created_at', 'updated_at', 'status', 'operator_name'], 'safe'],
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
        $query = CustomerInvitationActivityPrize::find();

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
            'type' => $this->type,
            'operator_id' => $this->operator_id,
            'range_start' => $this->range_start,
            'range_end' => $this->range_end,
            'expect_quantity' => $this->expect_quantity,
            'real_quantity' => $this->real_quantity,
            'activity_id'=>$this->activity_id,
            'num'=>$this->num,
            'company_id'=>$this->company_id
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'name', $this->name])
        ->andFilterWhere(['like', 'operator_name', $this->operator_name]);
        $query->orderBy('level_type');
        return $dataProvider;
    }
}
