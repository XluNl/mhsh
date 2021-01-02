<?php


namespace backend\models\searches;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\User;
use common\models\UserInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class UserSearch extends User
{
    public $start_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'delivery_id'], 'integer'],
            [['created_at', 'updated_at', 'phone','start_time','end_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'id' => 'ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'delivery_id' => '合伙人ID',
            'phone' => '联系电话',
            'start_time'=>'起始时间',
            'end_time'=>'截止时间',
        ]);
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
        $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$params['UserSearch']['company_id'],'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);

        $query = User::find();
        $query->alias('u');
        $query->select('u.*, i.phone, o.order_count, o.amount_sum');
        $query->join('LEFT JOIN', UserInfo::tableName() . ' as i', 'u.user_info_id = i.id');

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

        $query->join('LEFT JOIN', '( select count(*) as order_count, sum(`need_amount`) as amount_sum, delivery_id, customer_id from ' . Order::tableName() .' where order_owner = ' . GoodsConstantEnum::OWNER_SELF . ' and order_status = ' . Order::ORDER_STATUS_COMPLETE . ' and delivery_id is not null ' .$time. ' group by customer_id) as o', 'o.customer_id = u.id');
        $query->andFilterWhere(['u.delivery_id'=>$partner]);

        // grid filtering conditions
        $query->andFilterWhere([
            'u.id' => $this->id,
            'u.created_at' => $this->created_at,
            'u.updated_at' => $this->updated_at,
            'u.status' => $this->status,
            'u.openid' => $this->openid,
            'u.unionid' => $this->unionid,
            'u.username' => $this->username,
            'u.headimgurl' => $this->headimgurl,
            'u.password' => $this->password,
            'u.salt' => $this->salt,
            'u.create_ip' => $this->create_ip,
            'u.last_login' => $this->last_login,
            'u.auth_key' => $this->auth_key,
            'u.access_token' => $this->access_token,
            'u.user_type' => $this->user_type,
            'u.user_info_id' => $this->user_info_id,
            'u.delivery_id' => $this->delivery_id,
            'u.sex' => $this->sex,
        ]);

        $query->andFilterWhere(['like', 'u.nickname', $this->nickname])
            ->andFilterWhere(['like', 'i.phone', $this->phone]);
        $query->orderBy('u.id desc');

//        echo $query->createCommand()->getRawSql();;
//        exit();
        return $dataProvider;
    }
}