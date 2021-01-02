<?php

namespace backend\models\searches;

use common\models\Banner;
use common\models\CommonStatus;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BannerSearch represents the model behind the search form about `common\models\Banner`.
 */
class BannerSearch extends Banner
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'type', 'display_order', 'operator_id', 'status'], 'integer'],
            [['created_at', 'updated_at', 'name', 'images', 'messages', 'operator_name', 'online_time', 'offline_time'], 'safe'],
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
        $query = Banner::find();

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
            'display_order' => $this->display_order,
            'operator_id' => $this->operator_id,
            'online_time' => $this->online_time,
            'offline_time' => $this->offline_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'images', $this->images])
            ->andFilterWhere(['like', 'messages', $this->messages])
            ->andFilterWhere(['like', 'operator_name', $this->operator_name]);
        $query->andFilterWhere(['status'=>CommonStatus::$activeStatusArr]);
        $query->orderBy('updated_at desc');
        return $dataProvider;
    }
}
