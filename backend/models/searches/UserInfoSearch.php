<?php

namespace backend\models\searches;

use common\models\UserInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserInfoSearch represents the model behind the search form about `common\models\UserInfo`.
 */
class UserInfoSearch extends UserInfo
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'province_id', 'city_id', 'county_id', 'is_customer', 'is_popularizer', 'is_delivery'], 'integer'],
            [['created_at', 'updated_at', 'phone', 'em_phone', 'wx_number', 'email', 'nickname', 'realname', 'occupation', 'community', 'address'], 'safe'],
            [['lat', 'lng'], 'number'],
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
        $query = UserInfo::find();

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
            'status' => $this->status,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'county_id' => $this->county_id,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'is_customer' => $this->is_customer,
            'is_popularizer' => $this->is_popularizer,
            'is_delivery' => $this->is_delivery,
        ]);

        $query->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'em_phone', $this->em_phone])
            ->andFilterWhere(['like', 'wx_number', $this->wx_number])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'realname', $this->realname])
            ->andFilterWhere(['like', 'occupation', $this->occupation])
            ->andFilterWhere(['like', 'community', $this->community])
            ->andFilterWhere(['like', 'address', $this->address]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
