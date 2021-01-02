<?php

namespace backend\models\searches;

use common\models\CustomerInvitationActivityResult;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CustomerInvitationActivityResultSearch represents the model behind the search form about `common\models\CustomerInvitationActivityResult`.
 */
class CustomerInvitationActivityResultSearch extends CustomerInvitationActivityResult
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'activity_id', 'customer_id', 'invitation_count', 'invitation_order_count'], 'integer'],
            [['created_at', 'updated_at', 'customer_name', 'customer_phone', 'prizes', 'children'], 'safe'],
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
        $query = CustomerInvitationActivityResult::find();

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
            'activity_id' => $this->activity_id,
            'customer_id' => $this->customer_id,
            'invitation_count' => $this->invitation_count,
            'invitation_order_count' => $this->invitation_order_count,
        ]);

        $query->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'customer_phone', $this->customer_phone])
            ->andFilterWhere(['like', 'prizes', $this->prizes])
            ->andFilterWhere(['like', 'children', $this->children]);

        return $dataProvider;
    }
}
