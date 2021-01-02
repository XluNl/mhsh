<?php

namespace backend\models\searches;

use common\models\CouponBatchDrawLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CouponLogSearch represents the model behind the search form about `common\models\CouponLog`.
 */
class CouponBatchLogSearch extends CouponBatchDrawLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'batch_id', 'customer_id', 'company_id'], 'integer'],
            [['remark', 'created_at', 'updated_at'], 'safe'],
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
        $query = CouponBatchDrawLog::find();

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
            'batch_id' => $this->batch_id,
            'customer_id' => $this->customer_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}
