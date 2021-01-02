<?php

namespace backend\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\GroupRoom;

/**
 * GroupRoomSearch represents the model behind the search form of `common\models\GroupRoom`.
 */
class GroupRoomSearch extends GroupRoom
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'team_id', 'place_count', 'paid_order_count', 'max_level', 'min_level', 'status', 'company_id'], 'integer'],
            [['active_no', 'room_no', 'team_name', 'finished_at', 'msg', 'created_at', 'updated_at'], 'safe'],
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
        $query = GroupRoom::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
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
            'team_id' => $this->team_id,
            'place_count' => $this->place_count,
            'paid_order_count' => $this->paid_order_count,
            'max_level' => $this->max_level,
            'min_level' => $this->min_level,
            'status' => $this->status,
            'finished_at' => $this->finished_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'active_no', $this->active_no])
            ->andFilterWhere(['like', 'room_no', $this->room_no])
            ->andFilterWhere(['like', 'team_name', $this->team_name])
            ->andFilterWhere(['like', 'msg', $this->msg]);
        return $dataProvider;
    }
}