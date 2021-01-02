<?php

namespace backend\models\searches;

use common\models\OrderCustomerService;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderCustomerServiceSearch represents the model behind the search form about `common\models\OrderCustomerService`.
 */
class OrderCustomerServiceSearch extends OrderCustomerService
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'status', 'audit_level', 'type', 'delivery_id', 'company_id'], 'integer'],
            [['created_at', 'updated_at', 'order_no','remark'], 'safe'],
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
        $query = OrderCustomerService::find();

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
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'audit_level' => $this->audit_level,
            'type' => $this->type,
            'delivery_id' => $this->delivery_id,
            'company_id' => $this->company_id,
            'remark'=>$this->remark,
            'audit_remark'=>$this->audit_remark,
            'images'=>$this->images
        ]);

        $query->andFilterWhere(['like', 'order_no', $this->order_no]);
        $query->with(['delivery','order']);
        $query->orderBy('created_at desc');
        return $dataProvider;
    }
}
