<?php


namespace backend\models\searches;


use backend\services\DeliveryManagementService;
use common\models\OrderGoods;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 *
 * Class DeliveryManagementSearch
 * @package backend\models\searches
 */
class DeliveryManagementSearch extends Model
{


    public $expect_arrive_time;
    public $company_id;

    public $order_time_start;

    public $order_time_end;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['expect_arrive_time','required'],
            [['expect_arrive_time','company_id','order_time_start','order_time_end'], 'safe'],
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
            'order_time_start'=>'订单时间启',
            'order_time_end'=>'订单时间终',
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
        $query = DeliveryManagementService::getDeliveryDataByExpectArriveTimeB($this->expect_arrive_time,$this->order_time_start,$this->order_time_end,$this->company_id);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'schedule_id',
        ]);

        return $dataProvider;
    }

}