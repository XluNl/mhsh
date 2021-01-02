<?php


namespace backend\models\forms;


use backend\services\DeliveryManagementService;
use common\models\OrderGoods;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 *
 * Class OrderDeliverySearch
 * @package backend\models\forms
 */
class OrderDeliverySearchForms extends Model
{

    public $owner_type;
    public $expect_arrive_time;
    public $company_id;
    public $less_goods_num;
    public $less_goods_amount;
    public $order_status;
    public $delivery_id;
    public $deliveryOptions = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['expect_arrive_time','required'],
            [['expect_arrive_time','owner_type','company_id','less_goods_num','order_status','less_goods_amount','delivery_id'], 'safe'],
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

    public function attributeLabels(){
        return [
            'expect_arrive_time'=>'预计送达日期',
            'company_id'=>'公司id',
            'less_goods_num'=>'少于配送的商品数量',
            'less_goods_amount'=>'少于配送的商品金额',
            'order_status'=>'订单状态',
            'owner_type'=>'订单类型',
            'delivery_id'=>'配送团长',
        ];
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
        $query  =(new Query())->from(OrderGoods::tableName());

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (StringUtils::isBlank($this->expect_arrive_time)){
            $this->expect_arrive_time = DateTimeUtils::formatYearAndMonthAndDay(time(),false);
        }
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }
        $query = DeliveryManagementService::getOrderDeliveryByExpectArriveTime($this->owner_type,$this->expect_arrive_time,$this->company_id,$this->less_goods_num,$this->less_goods_amount,$this->order_status,$this->delivery_id);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'order_no',
        ]);
        return $dataProvider;
    }

}