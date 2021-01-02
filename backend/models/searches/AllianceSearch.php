<?php

namespace backend\models\searches;

use common\models\Alliance;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AllianceSearch represents the model behind the search form about `common\models\Alliance`.
 */
class AllianceSearch extends Alliance
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'company_id', 'province_id', 'city_id', 'county_id','type'], 'integer'],
            [['created_at', 'updated_at', 'head_img_url', 'nickname', 'realname', 'phone', 'em_phone', 'wx_number', 'community', 'address', 'status', 'store_images', 'qualification_images'], 'safe'],
            [['lng', 'lat'], 'number'],
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
        $query = Alliance::find();

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
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'company_id' => $this->company_id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'county_id' => $this->county_id,
            'lng' => $this->lng,
            'lat' => $this->lat,
            'type'=> $this->type
        ]);

        $query->andFilterWhere(['like', 'head_img_url', $this->head_img_url])
            ->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'realname', $this->realname])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'em_phone', $this->em_phone])
            ->andFilterWhere(['like', 'wx_number', $this->wx_number])
            ->andFilterWhere(['like', 'community', $this->community])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'store_images', $this->store_images])
            ->andFilterWhere(['like', 'qualification_images', $this->qualification_images]);

        return $dataProvider;
    }
}
