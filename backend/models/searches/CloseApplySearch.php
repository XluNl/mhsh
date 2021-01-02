<?php

namespace backend\models\searches;

use common\models\CloseApply;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CloseApplySearch represents the model behind the search form about `common\models\CloseApply`.
 */
class CloseApplySearch extends CloseApply
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'company_id', 'biz_type', 'biz_id', 'status', 'operator_id','action'], 'integer'],
            [['created_at', 'updated_at', 'images', 'remark', 'operator_name','name','phone','operator_remark'], 'safe'],
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
        $query = CloseApply::find();

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
            'biz_type' => $this->biz_type,
            'biz_id' => $this->biz_id,
            'status' => $this->status,
            'action' => $this->action,
            'operator_id' => $this->operator_id,
        ]);
        $query->andFilterWhere(['status'=>[
            CloseApply::ACTION_APPLY,
            CloseApply::ACTION_ACCEPT,
            CloseApply::ACTION_DENY,
            CloseApply::ACTION_CANCEL
        ]]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'images', $this->images])
            ->andFilterWhere(['like', 'remark', $this->remark])
            ->andFilterWhere(['like', 'operator_name', $this->operator_name])
            ->andFilterWhere(['like', 'operator_remark', $this->operator_remark]);

        return $dataProvider;
    }
}
