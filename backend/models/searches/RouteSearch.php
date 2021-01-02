<?php

namespace backend\models\searches;

use common\models\Route;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * RouteSearch represents the model behind the search form about `common\models\Route`.
 */
class RouteSearch extends Route
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'status'], 'integer'],
            [['created_at', 'updated_at', 'nickname', 'realname', 'phone', 'em_phone'], 'safe'],
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
        $query = Route::find();

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
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'nickname', trim($this->nickname)])
            ->andFilterWhere(['like', 'realname', trim($this->realname)])
            ->andFilterWhere(['like', 'phone', trim($this->phone)])
            ->andFilterWhere(['like', 'em_phone', trim($this->em_phone)]);
        $query->andFilterWhere(['status'=>[Route::STATUS_ACTIVE,Route::STATUS_DISABLED]]);
        return $dataProvider;
    }
}
