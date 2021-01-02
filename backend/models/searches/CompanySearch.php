<?php

namespace backend\models\searches;

use common\models\Company;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CompanySearch represents the model behind the search form about `common\models\Company`.
 */
class CompanySearch extends Company
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'province_id', 'city_id', 'county_id'], 'integer'],
            [['name', 'address', 'contact', 'office_phone', 'telphone', 'service_phone', 'fax', 'zip_code', 'status', 'email', 'created_at', 'updated_at'], 'safe'],
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
        $query = Company::find();

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
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'county_id' => $this->county_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'contact', $this->contact])
            ->andFilterWhere(['like', 'office_phone', $this->office_phone])
            ->andFilterWhere(['like', 'telphone', $this->telphone])
            ->andFilterWhere(['like', 'service_phone', $this->service_phone])
            ->andFilterWhere(['like', 'fax', $this->fax])
            ->andFilterWhere(['like', 'zip_code', $this->zip_code])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
