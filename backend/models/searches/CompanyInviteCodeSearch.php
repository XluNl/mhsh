<?php

namespace backend\models\searches;

use common\models\CompanyInviteCode;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CompanyInviteCodeSearch represents the model behind the search form about `common\models\CompanyInviteCode`.
 */
class CompanyInviteCodeSearch extends CompanyInviteCode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id'], 'integer'],
            [['created_at', 'updated_at', 'business_invite_image', 'alliance_invite_image'], 'safe'],
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
        $query = CompanyInviteCode::find();

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
        ]);

        $query->andFilterWhere(['like', 'business_invite_image', $this->business_invite_image])
            ->andFilterWhere(['like', 'alliance_invite_image', $this->alliance_invite_image]);

        return $dataProvider;
    }
}
