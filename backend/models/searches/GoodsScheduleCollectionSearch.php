<?php

namespace backend\models\searches;

use common\models\Alliance;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\GoodsScheduleCollection;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GoodsScheduleCollectionSearch represents the model behind the search form about `common\models\GoodsScheduleCollection`.
 * @property array $ownerTypeOptions
 */
class GoodsScheduleCollectionSearch extends GoodsScheduleCollection
{

    public $ownerTypeOptions = [];
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'operation_id', 'company_id', 'status','owner_id','owner_type'], 'integer'],
            [['collection_name', 'operation_name', 'created_at', 'updated_at','display_start', 'display_end', 'online_time', 'offline_time'], 'safe'],
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
        $goodsScheduleCollectionTable=GoodsScheduleCollection::tableName();
        $deliveryTable = Delivery::tableName();
        $allianceTable = Alliance::tableName();

        $OWNER_TYPE_SELF = GoodsConstantEnum::OWNER_SELF;
        $OWNER_TYPE_HA = GoodsConstantEnum::OWNER_HA;
        $OWNER_TYPE_DELIVERY = GoodsConstantEnum::OWNER_DELIVERY;

        $query = GoodsScheduleCollection::find();

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
            "{$goodsScheduleCollectionTable}.id" => $this->id,
            "{$goodsScheduleCollectionTable}.operation_id" => $this->operation_id,
            "{$goodsScheduleCollectionTable}.created_at" => $this->created_at,
            "{$goodsScheduleCollectionTable}.updated_at" => $this->updated_at,
            "{$goodsScheduleCollectionTable}.company_id" => $this->company_id,
            "{$goodsScheduleCollectionTable}.status" => CommonStatus::STATUS_ACTIVE,
            "{$goodsScheduleCollectionTable}.display_start"=>$this->display_start,
            "{$goodsScheduleCollectionTable}.display_end"=>$this->display_end,
            "{$goodsScheduleCollectionTable}.online_time"=>$this->online_time,
            "{$goodsScheduleCollectionTable}.offline_time"=>$this->offline_time,
            "{$goodsScheduleCollectionTable}.owner_id"=>$this->owner_id,
            "{$goodsScheduleCollectionTable}.owner_type"=>$this->owner_type,
        ]);

        $query->select(
            [
                "{$goodsScheduleCollectionTable}.*",
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

        $query->leftJoin($allianceTable,"{$goodsScheduleCollectionTable}.owner_type = {$OWNER_TYPE_HA} and {$allianceTable}.id={$goodsScheduleCollectionTable}.owner_id");
        $query->leftJoin($deliveryTable,"{$goodsScheduleCollectionTable}.owner_type = {$OWNER_TYPE_DELIVERY} and {$deliveryTable}.id={$goodsScheduleCollectionTable}.owner_id");

        $query->andFilterWhere(['like', 'collection_name', trim($this->collection_name)])
            ->andFilterWhere(['like', 'operation_name', $this->operation_name]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
