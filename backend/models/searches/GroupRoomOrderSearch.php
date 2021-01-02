<?php

namespace backend\models\searches;

use common\models\GoodsConstantEnum;
use common\models\GroupRoom;
use common\models\GroupRoomOrder;
use common\models\Order;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class GroupRoomOrderSearch extends GroupRoomOrder
{	

    public $owner_type;
    public $goods_id;
    public $goodsOptions= [];
    public $order_status;

    public $start_time;
    public $end_time;
    public $status;
    public $phone;
    public function rules()
    {
        return [
            [['id', 'customer_id', 'schedule_amount', 'active_amount', 'company_id','order_status','owner_type','goods_id'], 'integer'],
            [['room_no','active_no', 'order_no', 'created_at','status', 'updated_at','phone', 'start_time', 'end_time'], 'safe'],
        ];
    }

    public function attributeLabels(){
        return array_merge(parent::attributeLabels(),[
            'owner_type'=>'归属',
            'goods_id' => '商品',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'status' =>  '拼团状态',//拼团状态 0进行中 1已成团
            'phone' => '拼团手机号',
            'order_status'=>'订单状态',
        ]);
    }


    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {

        $groupRoomOrderTable = GroupRoomOrder::tableName();
        $groupRoomTable = GroupRoom::tableName();
        $orderTable = Order::tableName();

        $query = GroupRoomOrder::find();

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
            "{$groupRoomOrderTable}.id" => $this->id,
            "{$groupRoomOrderTable}.customer_id" => $this->customer_id,
            "{$groupRoomOrderTable}.schedule_amount" => $this->schedule_amount,
            "{$groupRoomOrderTable}.active_amount" => $this->active_amount,
            "{$groupRoomOrderTable}.company_id" => $this->company_id,
            "{$groupRoomOrderTable}.created_at" => $this->created_at,
            "{$groupRoomOrderTable}.updated_at" => $this->updated_at,
        ]);

        $query
            ->andFilterWhere(['like', "{$groupRoomOrderTable}.active_no", $this->active_no])
            ->andFilterWhere(['like', "{$groupRoomOrderTable}.room_no", $this->room_no])
            ->andFilterWhere(['like', "{$groupRoomOrderTable}.order_no", $this->order_no]);

        $query->with(['order','order.goods','groupRoom','groupActive']);


        if (StringUtils::isNotBlank($this->phone)||StringUtils::isNotBlank($this->order_status)||StringUtils::isNotBlank($this->owner_type)){
            $query->leftJoin($orderTable,"{$groupRoomOrderTable}.order_no = {$orderTable}.order_no");
            if (StringUtils::isNotBlank($this->phone)){
                $query->andFilterWhere([
                    "{$orderTable}.accept_mobile" => $this->phone,
                ]);
            }
            if (StringUtils::isNotBlank($this->order_status)){
                $query->andFilterWhere([
                    "{$orderTable}.order_status" => $this->order_status,
                ]);
            }
            if (StringUtils::isNotBlank($this->owner_type)){
                $query->andFilterWhere([
                    "{$orderTable}.order_owner" => $this->owner_type,
                ]);
            }

        }

        if (StringUtils::isNotBlank($this->status)){
            $query->leftJoin($groupRoomTable,"{$groupRoomOrderTable}.room_no = {$groupRoomTable}.room_no");
            $query->andFilterWhere([
                "{$groupRoomTable}.status" => $this->status,
            ]);
        }

        if (StringUtils::isNotBlank($this->goods_id)){
            $query->joinWith(['order.goods'=>function($query){
                $query->where(['like','goods_id',trim($this->goods_id)]);
            }]);
        }

        $query->orderBy("{$groupRoomOrderTable}.updated_at desc");


       /* $groupRoomOrder = GroupRoomOrder::tableName();
        $query = groupRoomOrder::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'order_no' => $this->order_no,
            'group_no' => $this->group_no,
            "{$groupRoomOrder}.company_id" => $this->company_id,
        ]);
        $query->joinWith(['groupRoom'=>function($query){
            $query->where(['status'=>$this->status]);
        }]);

        if(trim($this->goods_name)){
            $query->joinWith(['groupRoom.activeInfo.shedule.goods'=>function($query){
                $query->where(['like','goods_name',trim($this->goods_name)]);
            }]);
        }
        if(trim($this->phone_number)){
            $query->joinWith(['groupRoom.teamInfo'=>function($query){
                $query->where(['like','phone',trim($this->phone_number)]);
            }]);
        }
        if($this->start_time || $this->end_time){
            $query->joinWith(['groupRoom.activeInfo.shedule'=>function($query){
                if($this->start_time){
                     $query->andWhere(['>=','online_time',$this->start_time]);
                }
                if($this->end_time){
                     $query->andWhere(['<=','offline_time',$this->end_time]);
                }
            }]);
        }
        $query->orderBy("{$groupRoomOrder}.id desc");*/
        return $dataProvider;
    }
}
