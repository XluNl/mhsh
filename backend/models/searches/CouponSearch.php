<?php

namespace backend\models\searches;

use common\models\Coupon;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CouponSearch represents the model behind the search form about `common\models\Coupon`.
 */
class CouponSearch extends Coupon
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'startup', 'discount', 'type', 'customer_id', 'company_id','owner_type','owner_id'], 'integer'],
            [['coupon_no', 'name', 'start_time', 'end_time', 'status', 'order_no', 'use_time', 'batch', 'remark', 'created_at', 'updated_at', 'limit_type', 'limit_type_params', 'restore'], 'safe'],
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
        $query = Coupon::find();

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
            'startup' => $this->startup,
            'discount' => $this->discount,
            'type' => $this->type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'use_time' => $this->use_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer_id' => $this->customer_id,
            'company_id' => $this->company_id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
        ]);

        $query->andFilterWhere(['like', 'coupon_no', $this->coupon_no])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'order_no', $this->order_no])
            ->andFilterWhere(['like', 'batch', $this->batch])
            ->andFilterWhere(['like', 'remark', $this->remark])
            ->andFilterWhere(['like', 'limit_type', $this->limit_type])
            ->andFilterWhere(['like', 'limit_type_params', $this->limit_type_params])
            ->andFilterWhere(['like', 'restore', $this->restore]);
        $query->orderBy("id desc");
        return $dataProvider;
    }
}
