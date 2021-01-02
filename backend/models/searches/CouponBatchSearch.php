<?php

namespace backend\models\searches;

use common\models\Alliance;
use common\models\CouponBatch;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CouponBatchSearch represents the model behind the search form about `common\models\CouponBatch`.
 */
class CouponBatchSearch extends CouponBatch
{
    public $ownerTypeOptions = [];
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'startup', 'discount', 'type', 'use_limit_type', 'restore', 'operator_id', 'status', 'draw_limit_type', 'draw_limit_type_params', 'draw_amount', 'amount', 'version', 'is_public', 'draw_customer_type', 'is_pop', 'owner_type', 'coupon_type', 'user_time_type', 'owner_id'], 'integer'],
            [['created_at', 'updated_at', 'batch_no', 'name', 'coupon_name', 'remark', 'use_limit_type_params', 'draw_start_time', 'draw_end_time', 'use_start_time', 'use_end_time', 'operator_name', 'draw_customer_phones', 'use_time_feature'], 'safe'],
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
        $couponBatchTable=CouponBatch::tableName();
        $deliveryTable = Delivery::tableName();
        $allianceTable = Alliance::tableName();

        $OWNER_TYPE_SELF = GoodsConstantEnum::OWNER_SELF;
        $OWNER_TYPE_HA = GoodsConstantEnum::OWNER_HA;
        $OWNER_TYPE_DELIVERY = GoodsConstantEnum::OWNER_DELIVERY;
        $query = CouponBatch::find();

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

        $query->select(
            [
                "{$couponBatchTable}.*",
                "CASE 
                WHEN owner_type = {$OWNER_TYPE_SELF} THEN
                    '代理商自营'
                WHEN owner_type = {$OWNER_TYPE_HA} THEN
                    {$allianceTable}.`nickname`
                WHEN owner_type = {$OWNER_TYPE_DELIVERY} THEN
                    {$deliveryTable}.`realname`
                ELSE
                    ''	
                END AS owner_name"
            ]
        );

        // grid filtering conditions
        $query->andFilterWhere([
            "{$couponBatchTable}.id" => $this->id,
            "{$couponBatchTable}.created_at" => $this->created_at,
            "{$couponBatchTable}.updated_at" => $this->updated_at,
            "{$couponBatchTable}.company_id" => $this->company_id,
            "{$couponBatchTable}.startup" => $this->startup,
            "{$couponBatchTable}.discount" => $this->discount,
            "{$couponBatchTable}.type" => $this->type,
            "{$couponBatchTable}.use_limit_type" => $this->use_limit_type,
            "{$couponBatchTable}.restore" => $this->restore,
            "{$couponBatchTable}.draw_start_time" => $this->draw_start_time,
            "{$couponBatchTable}.draw_end_time" => $this->draw_end_time,
            "{$couponBatchTable}.use_start_time" => $this->use_start_time,
            "{$couponBatchTable}.use_end_time" => $this->use_end_time,
            "{$couponBatchTable}.operator_id" => $this->operator_id,
            "{$couponBatchTable}.status" => $this->status,
            "{$couponBatchTable}.draw_limit_type" => $this->draw_limit_type,
            "{$couponBatchTable}.draw_limit_type_params" => $this->draw_limit_type_params,
            "{$couponBatchTable}.draw_amount" => $this->draw_amount,
            "{$couponBatchTable}.amount" => $this->amount,
            "{$couponBatchTable}.version" => $this->version,
            "{$couponBatchTable}.is_public" => $this->is_public,
            "{$couponBatchTable}.draw_customer_type" => $this->draw_customer_type,
            "{$couponBatchTable}.batch_no" => $this->batch_no,
            "{$couponBatchTable}.is_pop" => $this->is_pop,
            "{$couponBatchTable}.owner_type" => $this->owner_type,
            "{$couponBatchTable}.owner_id" => $this->owner_id,
            "{$couponBatchTable}.coupon_type" => $this->coupon_type,
        ]);

        $query->andFilterWhere(['like', "{$couponBatchTable}.name", $this->name])
            ->andFilterWhere(['like', "{$couponBatchTable}.coupon_name", $this->coupon_name])
            ->andFilterWhere(['like', "{$couponBatchTable}.remark", $this->remark])
            ->andFilterWhere(['like', "{$couponBatchTable}.use_limit_type_params", $this->use_limit_type_params])
            ->andFilterWhere(['like', "{$couponBatchTable}.operator_name", $this->operator_name])
            ->andFilterWhere(['like', "{$couponBatchTable}.draw_customer_phones", $this->operator_name]);
        $query->andFilterWhere(["{$couponBatchTable}.status"=>array_keys(CouponBatch::$statusDisplayArr)]);

        $query->leftJoin($allianceTable,"{$couponBatchTable}.owner_type = {$OWNER_TYPE_HA} and {$allianceTable}.id={$couponBatchTable}.owner_id");
        $query->leftJoin($deliveryTable,"{$couponBatchTable}.owner_type = {$OWNER_TYPE_DELIVERY} and {$deliveryTable}.id={$couponBatchTable}.owner_id");

        $query->orderBy('id desc');
        return $dataProvider;
    }
}
