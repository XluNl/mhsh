<?php

namespace backend\models\searches;

use common\models\Goods;
use common\models\GoodsSchedule;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\GoodsConstantEnum;

/**
 * GoodsScheduleSearch represents the model behind the search form about `common\models\GoodsSchedule`.
 * @property string $goods_name
 * @property string $schedule_date
 */
class GoodsScheduleSearch extends GoodsSchedule
{

    public $goods_name;
    public $goods_status;
    public $sku_status;
    public $schedule_date;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'goods_id', 'sku_id', 'price', 'schedule_stock', 'schedule_limit_quantity', 'display_order', 'operation_id', 'company_id','collection_id','owner_type','owner_id','sku_status','goods_status','recommend'], 'integer'],
            [['goods_name','schedule_name','schedule_date','display_start', 'display_end', 'online_time', 'offline_time', 'validity_start', 'validity_end', 'operation_name', 'created_at', 'updated_at','updated_at','expect_arrive_time','schedule_display_channel','schedule_status'], 'safe'],
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
        return array_merge(parent::attributeLabels(),[
            'goods_name'=>'商品名称',
            'schedule_date'=>'指定日期排期',
            'goods_status'=>'商品状态',
            'sku_status'=>'属性状态',
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
        $goodsTable = Goods::tableName();
        $scheduleTable = GoodsSchedule::tableName();
        $query = GoodsSchedule::find();

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
            "{$scheduleTable}.id" => $this->id,
            "{$scheduleTable}.goods_id" => $this->goods_id,
            "{$scheduleTable}.sku_id" => $this->sku_id,
            "{$scheduleTable}.price" => $this->price,
            "{$scheduleTable}.schedule_status" => $this->schedule_status,
            "{$scheduleTable}.schedule_stock" => $this->schedule_stock,
            "{$scheduleTable}.schedule_limit_quantity" => $this->schedule_limit_quantity,
            "{$scheduleTable}.display_order" => $this->display_order,
            "{$scheduleTable}.schedule_display_channel" => $this->schedule_display_channel,
            "{$scheduleTable}.display_start" => $this->display_start,
            "{$scheduleTable}.display_end" => $this->display_end,
            "{$scheduleTable}.online_time" => $this->online_time,
            "{$scheduleTable}.offline_time" => $this->offline_time,
            "{$scheduleTable}.validity_start" => $this->validity_start,
            "{$scheduleTable}.validity_end" => $this->validity_end,
            "{$scheduleTable}.operation_id" => $this->operation_id,
            "{$scheduleTable}.created_at" => $this->created_at,
            "{$scheduleTable}.updated_at" => $this->updated_at,
            "{$scheduleTable}.expect_arrive_time" => $this->expect_arrive_time,
            "{$scheduleTable}.company_id" => $this->company_id,
            "{$scheduleTable}.schedule_sold" => $this->schedule_sold,
            "{$scheduleTable}.collection_id" => $this->collection_id,
            "{$scheduleTable}.owner_type" => $this->owner_type,
            "{$scheduleTable}.owner_id" => $this->owner_id,
            "{$scheduleTable}.recommend" => $this->recommend,
        ]);

        $query->andFilterWhere(['like', 'operation_name', $this->operation_name])
            ->andFilterWhere(['like', 'schedule_name', $this->schedule_name]);
        if (!StringUtils::isBlank($this->schedule_date)){
            $query->andFilterWhere([
                'and',
                ['<=',"{$scheduleTable}.online_time",DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($this->schedule_date))],
                ['>=',"{$scheduleTable}.offline_time",DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($this->schedule_date))]
            ]);
        }

        /*// 团购活动选排期/banner 链接
        if(!empty($this->owner_type)){
            if($this->group_active){
                $query->andWhere(["{$scheduleTable}.schedule_display_channel"=>GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_GROUP]);
            }else{
                $query->andWhere(["{$scheduleTable}.schedule_display_channel"=>GoodsConstantEnum::$scheduleDisplayChannelMap[$this->owner_type]]);
            }
        }*/



        if (StringUtils::isNotBlank($this->goods_name)||StringUtils::isNotBlank($this->goods_status)||StringUtils::isNotBlank($this->sku_status)){
            if (StringUtils::isNotBlank($this->sku_status)){
                $query->joinWith(['goodsSku'=>function($query){
                    $query->where(['sku_status'=>GoodsConstantEnum::STATUS_UP]);
                }]);
            }
            if (StringUtils::isNotBlank($this->goods_status)){
                $query->joinWith(['goods'=>function($query){
                    $query->where(['goods_status'=>GoodsConstantEnum::STATUS_UP]);
                    if(StringUtils::isNotBlank($this->goods_name)){
                        $query->andFilterWhere(['like', 'goods_name', trim($this->goods_name)]);
                    }
                }]);
            }
            else if (StringUtils::isNotBlank($this->goods_name)){
                $query->joinWith(['goods'=>function($query){
                    $query->andFilterWhere(['like', 'goods_name', trim($this->goods_name)]);
                }]);
            }
        }
        $query->with(['goods','goodsSku']);
        $query->orderBy("{$scheduleTable}.created_at desc");
        return $dataProvider;
    }
}
