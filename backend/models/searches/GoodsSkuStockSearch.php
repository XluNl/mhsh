<?php

namespace backend\models\searches;

use common\models\Goods;
use common\models\GoodsSkuStock;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GoodsSkuStockSearch represents the model behind the search form about `common\models\GoodsSkuStock`.
 */
class GoodsSkuStockSearch extends GoodsSkuStock
{
    public $goods_name;
    public $start_time;
    public $end_time;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'type', 'num', 'operator_id', 'goods_id', 'sku_id','schedule_id'], 'integer'],
            [['goods_name','created_at', 'updated_at', 'operator_name','remark','start_time','end_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return array_merge(parent::attributeLabels(),[
            'goods_name'=>'商品名称',
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
        if (empty($this->start_time)){
            $this->start_time = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong(time(),false));
        }
        if (empty($this->end_time)){
            $this->end_time = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong(time(),false));
        }
        $goodsTable = Goods::tableName();
        $goodsSkuStockTable = GoodsSkuStock::tableName();
        $query = GoodsSkuStock::find();

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
            "{$goodsSkuStockTable}.id" => $this->id,
            "{$goodsSkuStockTable}.created_at" => $this->created_at,
            "{$goodsSkuStockTable}.updated_at" => $this->updated_at,
            "{$goodsSkuStockTable}.company_id" => $this->company_id,
            "{$goodsSkuStockTable}.type" => $this->type,
            "{$goodsSkuStockTable}.num" => $this->num,
            "{$goodsSkuStockTable}.operator_role" => $this->operator_role,
            "{$goodsSkuStockTable}.operator_id" => $this->operator_id,
            "{$goodsSkuStockTable}.goods_id" => $this->goods_id,
            "{$goodsSkuStockTable}.sku_id" => $this->sku_id,
            "{$goodsSkuStockTable}.schedule_id" => $this->schedule_id,
        ]);
        if (!StringUtils::isBlank($this->goods_name)){
            $query->leftJoin(Goods::tableName(),"{$goodsTable}.id={$goodsSkuStockTable}.goods_id");
            $query->andFilterWhere(['like', 'goods_name', $this->goods_name]);
        }
        if (!StringUtils::isBlank($this->start_time)&&DateTimeUtils::checkFormat($this->start_time)){
            $startTime = DateTimeUtils::parseStandardWStrDate($this->start_time);
            $query->andFilterWhere(['>=', "{$goodsSkuStockTable}.created_at", $startTime]);
        }
        if (!StringUtils::isBlank($this->end_time)&&DateTimeUtils::checkFormat($this->end_time)){
            $endTime = DateTimeUtils::parseStandardWStrDate($this->end_time);
            $query->andFilterWhere(['<=', "{$goodsSkuStockTable}.created_at", $endTime]);
        }
        $query->andFilterWhere(['like', 'operator_name', $this->operator_name]);
        $query->andFilterWhere(['like', 'remark', $this->remark]);
        $query->with(['goods','goodsSku'])->orderBy('id desc');
        return $dataProvider;
    }
}
