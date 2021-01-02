<?php

namespace backend\models\searches;

use common\models\Order;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class OrderSearch extends Order
{
    public $order_time_start;
    public $order_time_end;
    public $complete_time_start;
    public $complete_time_end;
    public $goods_num_start;
    public $goods_num_end;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_status', 'goods_num', 'freight_amount', 'discount_amount', 'need_amount', 'need_amount_ac', 'real_amount', 'real_amount_ac', 'balance_pay_amount', 'three_pay_amount', 'order_type', 'order_owner', 'order_owner_id', 'pay_status', 'pay_amount', 'customer_id', 'customer_point', 'accept_province_id', 'accept_city_id', 'accept_county_id', 'accept_delivery_type', 'accept_period', 'share_rate_id_1', 'share_rate_id_2', 'delivery_id', 'evaluate', 'company_id','goods_num_start','goods_num_end'], 'integer'],
            [['order_no', 'discount_details', 'pay_id', 'pay_name', 'pay_type', 'pay_result', 'pay_time', 'send_time', 'accept_nickname', 'accept_name', 'accept_mobile', 'accept_community', 'accept_address', 'accept_time', 'completion_time', 'delivery_nickname', 'delivery_name', 'delivery_phone', 'delivery_code', 'created_at', 'updated_at', 'admin_note', 'admin_note','prepay_id'], 'safe'],
            [['order_time_start','order_time_end','complete_time_start','complete_time_end'],'safe'],
            [['goods_num_ac'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return array_merge(parent::attributeLabels(),[
            'order_time_start'=>'创建时间起始',
            'order_time_end'=>'创建时间截止',
            'complete_time_start'=>'完成时间起始',
            'complete_time_end'=>'完成时间截止',
            'goods_num_start'=>'商品总数起',
            'goods_num_end'=>'商品总数止',
        ]);
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
        $orderTable = Order::tableName();
        $query = Order::find();

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
            "{$orderTable}.id" => $this->id,
            "{$orderTable}.order_status" => $this->order_status,
            "{$orderTable}.goods_num" => $this->goods_num,
            "{$orderTable}.goods_num_ac" => $this->goods_num_ac,
            "{$orderTable}.freight_amount" => $this->freight_amount,
            "{$orderTable}.discount_amount" => $this->discount_amount,
            "{$orderTable}.need_amount" => $this->need_amount,
            "{$orderTable}.need_amount_ac" => $this->need_amount_ac,
            "{$orderTable}.real_amount" => $this->real_amount,
            "{$orderTable}.real_amount_ac" => $this->real_amount_ac,
            "{$orderTable}.balance_pay_amount" => $this->balance_pay_amount,
            "{$orderTable}.three_pay_amount" => $this->three_pay_amount,
            "{$orderTable}.order_type" => $this->order_type,
            "{$orderTable}.order_owner" => $this->order_owner,
            "{$orderTable}.order_owner_id" => $this->order_owner_id,
            "{$orderTable}.pay_status" => $this->pay_status,
            "{$orderTable}.pay_time" => $this->pay_time,
            "{$orderTable}.pay_amount" => $this->pay_amount,
            "{$orderTable}.send_time" => $this->send_time,
            "{$orderTable}.customer_id" => $this->customer_id,
            "{$orderTable}.customer_point" => $this->customer_point,
            "{$orderTable}.accept_province_id" => $this->accept_province_id,
            "{$orderTable}.accept_city_id" => $this->accept_city_id,
            "{$orderTable}.accept_county_id" => $this->accept_county_id,
            "{$orderTable}.accept_delivery_type" => $this->accept_delivery_type,
            "{$orderTable}.accept_period" => $this->accept_period,
            "{$orderTable}.accept_time" => $this->accept_time,
            "{$orderTable}.completion_time" => $this->completion_time,
            "{$orderTable}.share_rate_id_1" => $this->share_rate_id_1,
            "{$orderTable}.share_rate_id_2" => $this->share_rate_id_2,
            "{$orderTable}.delivery_id" => $this->delivery_id,
            "{$orderTable}.created_at" => $this->created_at,
            "{$orderTable}.updated_at" => $this->updated_at,
            "{$orderTable}.evaluate" => $this->evaluate,
            "{$orderTable}.company_id" => $this->company_id,
        ]);

        $query->andFilterWhere(['like', "{$orderTable}.order_no", trim($this->order_no)])
            ->andFilterWhere(['like', "{$orderTable}.discount_details", $this->discount_details])
            ->andFilterWhere(['like', "{$orderTable}.pay_id", $this->pay_id])
            ->andFilterWhere(['like', "{$orderTable}.prepay_id",$this->prepay_id])
            ->andFilterWhere(['like', "{$orderTable}.pay_name", $this->pay_name])
            ->andFilterWhere(['like', "{$orderTable}.pay_type", $this->pay_type])
            ->andFilterWhere(['like', "{$orderTable}.pay_result", $this->pay_result])
            ->andFilterWhere(['like', "{$orderTable}.accept_nickname", trim($this->accept_nickname)])
            ->andFilterWhere(['like', "{$orderTable}.accept_name",trim($this->accept_name)])
            ->andFilterWhere(['like', "{$orderTable}.accept_mobile", trim($this->accept_mobile)])
            ->andFilterWhere(['like', "{$orderTable}.accept_community", trim($this->accept_community)])
            ->andFilterWhere(['like', "{$orderTable}.accept_address", trim($this->accept_address)])
            ->andFilterWhere(['like', "{$orderTable}.delivery_nickname", trim($this->delivery_nickname)])
            ->andFilterWhere(['like', "{$orderTable}.delivery_name", trim($this->delivery_name)])
            ->andFilterWhere(['like', "{$orderTable}.delivery_phone", trim($this->delivery_phone)])
            ->andFilterWhere(['like', "{$orderTable}.delivery_code", trim($this->delivery_code)])
            ->andFilterWhere(['like', "{$orderTable}.order_note", $this->order_note])
            ->andFilterWhere(['like', "{$orderTable}.admin_note", $this->admin_note])
            ->andFilterWhere(['like', "{$orderTable}.cancel_remark", $this->admin_note]);

        if (StringUtils::isNotBlank($this->order_time_start)){
            $query->andFilterWhere(['>=', "{$orderTable}.created_at", $this->order_time_start]);
        }
        if (StringUtils::isNotBlank($this->order_time_end)){
            $query->andFilterWhere(['<=', "{$orderTable}.created_at", $this->order_time_end]);
        }

        if (StringUtils::isNotBlank($this->complete_time_start)){
            $query->andFilterWhere(['>=', "{$orderTable}.completion_time", $this->complete_time_start]);
        }
        if (StringUtils::isNotBlank($this->complete_time_end)){
            $query->andFilterWhere(['<=', "{$orderTable}.completion_time", $this->complete_time_end]);
        }

        if (StringUtils::isNotBlank($this->goods_num_start)){
            $query->andFilterWhere(['>=', "{$orderTable}.goods_num", $this->goods_num_start]);
        }
        if (StringUtils::isNotBlank($this->goods_num_end)){
            $query->andFilterWhere(['<=', "{$orderTable}.goods_num", $this->goods_num_end]);
        }

        $query->orderBy("{$orderTable}.created_at desc");
        return $dataProvider;
    }
}
