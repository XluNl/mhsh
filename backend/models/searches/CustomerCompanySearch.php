<?php

namespace backend\models\searches;

use common\models\Company;
use common\models\CustomerCompany;
use common\models\User;
use common\models\UserInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CustomerCompanySearch represents the model behind the search form about `common\models\CustomerCompany`.
 */
class CustomerCompanySearch extends CustomerCompany
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'province_id', 'city_id', 'county_id', 'is_customer', 'is_popularizer', 'is_delivery', 'company_id', 'user_id'], 'integer'],
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
        $query = CustomerCompany::find();
        $query->alias('c');
        $query->select('c.user_id, i.*');
        $query->join('LEFT JOIN', User::tableName() . ' as u', 'c.user_id = u.id');
        $query->join('LEFT JOIN', UserInfo::tableName() . ' as i', 'i.id = u.user_info_id');
        // add conditions that should always apply here
        $query->andWhere(['not', ['u.user_info_id' => null]]);

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
            'c.company_id' => $this->company_id,
            'i.id' => $this->id,
            'i.created_at' => $this->created_at,
            'i.updated_at' => $this->updated_at,
            'i.status' => $this->status,
            'i.province_id' => $this->province_id,
            'i.city_id' => $this->city_id,
            'i.county_id' => $this->county_id,
            'i.lat' => $this->lat,
            'i.lng' => $this->lng,
            'i.is_customer' => $this->is_customer,
            'i.is_popularizer' => $this->is_popularizer,
            'i.is_delivery' => $this->is_delivery,
        ]);

        $query->andFilterWhere(['like', 'i.phone', $this->phone])
            ->andFilterWhere(['like', 'i.em_phone', $this->em_phone])
            ->andFilterWhere(['like', 'i.wx_number', $this->wx_number])
            ->andFilterWhere(['like', 'i.email', $this->email])
            ->andFilterWhere(['like', 'i.nickname', $this->nickname])
            ->andFilterWhere(['like', 'i.realname', $this->realname])
            ->andFilterWhere(['like', 'i.occupation', $this->occupation])
            ->andFilterWhere(['like', 'i.community', $this->community])
            ->andFilterWhere(['like', 'i.address', $this->address]);
        $query->orderBy('i.id desc');

//        print_r($params);
//        print_r($query->createCommand()->getRawSql());
//        exit();

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function logSearch($params)
    {
        $query = CustomerCompany::find();
        $query->alias('c');
        $query->select('c.updated_at, m.name, m.address');
        $query->join('LEFT JOIN', Company::tableName() . ' as m', 'c.company_id = m.id');
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'c.user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'm.name', $this->name])
              ->andFilterWhere(['like', 'm.address', $this->address]);
        $query->orderBy('c.id desc');

//        print_r($params);
//        print_r($query->createCommand()->getRawSql());
//        exit();

        return $dataProvider;
    }
}
