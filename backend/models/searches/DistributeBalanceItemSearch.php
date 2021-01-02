<?php

namespace backend\models\searches;

use common\models\DistributeBalanceItem;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DistributeBalanceItemSearch represents the model behind the search form about `common\models\DistributeBalanceItem`.
 */
class DistributeBalanceItemSearch extends DistributeBalanceItem
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'user_id', 'biz_type', 'biz_id', 'type', 'type_id','distribute_balance_id', 'order_amount', 'amount', 'status', 'in_out', 'operator_id', 'remain_amount', 'action'], 'integer'],
            [['created_at', 'updated_at', 'order_no', 'distribute_detail', 'operator_name'], 'safe'],
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
        $query = DistributeBalanceItem::find();

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
            'user_id' => $this->user_id,
            'biz_type' => $this->biz_type,
            'biz_id' => $this->biz_id,
            'type' => $this->type,
            'type_id' => $this->type_id,
            'distribute_balance_id'=>$this->distribute_balance_id,
            'order_amount' => $this->order_amount,
            'amount' => $this->amount,
            'status' => $this->status,
            'in_out' => $this->in_out,
            'operator_id' => $this->operator_id,
            'remain_amount' => $this->remain_amount,
            'action' => $this->action,
        ]);

        $query->andFilterWhere(['like', 'order_no', $this->order_no])
            ->andFilterWhere(['like', 'distribute_detail', $this->distribute_detail])
            ->andFilterWhere(['like', 'operator_name', $this->operator_name]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
