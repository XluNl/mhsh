<?php

namespace backend\models\searches;

use common\models\CustomerInvitationActivity;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CustomerInvitationActivitySearch represents the model behind the search form about `common\models\CustomerInvitationActivity`.
 */
class CustomerInvitationActivitySearch extends CustomerInvitationActivity
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'settle_operator_id', 'operator_id', 'type','company_id','version'], 'integer'],
            [['created_at', 'updated_at', 'status', 'name', 'image', 'detail', 'show_start_time', 'show_end_time', 'settle_status', 'settle_time', 'settle_operator_name', 'operator_name', 'activity_start_time', 'activity_end_time'], 'safe'],
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
        $query = CustomerInvitationActivity::find();

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
            'show_start_time' => $this->show_start_time,
            'show_end_time' => $this->show_end_time,
            'settle_time' => $this->settle_time,
            'settle_operator_id' => $this->settle_operator_id,
            'operator_id' => $this->operator_id,
            'type' => $this->type,
            'activity_start_time' => $this->activity_start_time,
            'activity_end_time' => $this->activity_end_time,
            'company_id' => $this->company_id,
            'version'=>$this->version
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'detail', $this->detail])
            ->andFilterWhere(['like', 'settle_status', $this->settle_status])
            ->andFilterWhere(['like', 'settle_operator_name', $this->settle_operator_name])
            ->andFilterWhere(['like', 'operator_name', $this->operator_name]);

        return $dataProvider;
    }
}
