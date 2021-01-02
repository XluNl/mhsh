<?php

namespace backend\models\searches;

use common\models\CommonStatus;
use common\models\Delivery;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DeliverySearch represents the model behind the search form about `common\models\Delivery`.
 */
class DeliverySearch extends Delivery
{
    public $phone;
    public $start_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'province_id', 'city_id', 'county_id', 'status', 'company_id', 'min_amount_limit', 'allow_order', 'type', 'user_id', 'auth'], 'integer'],
            [['created_at', 'updated_at', 'nickname', 'phone', 'realname', 'community', 'address','head_img_url','start_time','end_time'], 'safe'],
            [['lng', 'lat'], 'number'],
        ];
    }

    public function attributeLabels(){
        return array_merge(parent::attributeLabels(),[
            'start_time'=>'起始时间',
            'end_time'=>'截止时间',
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
        $query = Delivery::find();

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
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'county_id' => $this->county_id,
            'lng' => $this->lng,
            'lat' => $this->lat,
            'status' => $this->status,
            'company_id' => $this->company_id,
            'min_amount_limit' => $this->min_amount_limit,
            'allow_order' => $this->allow_order,
            'type' => $this->type,
            'user_id' => $this->user_id,
            'head_img_url'=>$this->head_img_url,
        ]);

        $query->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'realname', $this->realname])
            ->andFilterWhere(['like', 'community', $this->community])
            ->andFilterWhere(['like', 'address', $this->address]);
        $query->andFilterWhere(['status'=>CommonStatus::STATUS_ACTIVE]);
        $query->orderBy('id desc');
        return $dataProvider;
    }

    /**
     * 带有合伙人粉丝数
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchFansCount($params)
    {
        $query = Delivery::find();
        $query->alias('d');
        $query->select('d.*, u.*');

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

        $start_time = strtotime($this->start_time);
        $end_time   = strtotime($this->end_time);

        if ($this->start_time&&!$this->end_time){
            $time = 'and `created_at` >= '.$start_time;
        }elseif (!$this->start_time && $this->end_time){
            $time = 'and `created_at` <= '.$end_time;
        }elseif ($this->start_time && $this->end_time){
            $time = 'and `created_at` between ' .$start_time. ' and ' .$end_time;
        }else{
            $time = '';
        }

        $query->join('LEFT JOIN', '(select delivery_id, count(*) as user_count from ' . User::tableName() . ' as u where `delivery_id` is not null ' .$time. ' group by `delivery_id`) as u', 'u.delivery_id = d.id');
        $query->andFilterWhere(['d.auth'=>Delivery::AUTH_STATUS_AUTH]);

        // grid filtering conditions
        $query->andFilterWhere([
            'd.id' => $this->id,
            'd.created_at' => $this->created_at,
            'd.updated_at' => $this->updated_at,
            'd.province_id' => $this->province_id,
            'd.city_id' => $this->city_id,
            'd.county_id' => $this->county_id,
            'd.lng' => $this->lng,
            'd.lat' => $this->lat,
            'd.status' => $this->status,
            'd.company_id' => $this->company_id,
            'd.min_amount_limit' => $this->min_amount_limit,
            'd.allow_order' => $this->allow_order,
            'd.type' => $this->type,
            'd.user_id' => $this->user_id,
            'd.head_img_url'=>$this->head_img_url,
        ]);

        $query->andFilterWhere(['like', 'd.nickname', $this->nickname])
            ->andFilterWhere(['like', 'd.phone', $this->phone])
            ->andFilterWhere(['like', 'd.realname', $this->realname])
            ->andFilterWhere(['like', 'd.community', $this->community])
            ->andFilterWhere(['like', 'd.address', $this->address]);
        $query->andFilterWhere(['d.status'=>CommonStatus::STATUS_ACTIVE]);
        $query->orderBy('d.id desc');
//        echo $query->createCommand()->getRawSql();
//        print_r('<pre>');
//        print_r($this);
//        exit();
        return $dataProvider;
    }
}
