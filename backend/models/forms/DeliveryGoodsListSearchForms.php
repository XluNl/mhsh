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
 * Class DeliveryGoodsListSearchForms
 * @package backend\models\forms
 */
class DeliveryGoodsListSearchForms extends Model
{

    public $owner_type;
    public $delivery_id;
    public $expect_arrive_time;
    public $company_id;
    public $deliveryOptions= [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['expect_arrive_time','required'],
            [['expect_arrive_time','owner_type','company_id','delivery_id'], 'safe'],
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
            'delivery_id'=>'配送团长',
            'owner_type'=>'订单类型',
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

        $query = DeliveryManagementService::getDeliveryGoodsList($this->owner_type,$this->expect_arrive_time,$this->company_id,$this->delivery_id);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);
        return $dataProvider;
    }

}