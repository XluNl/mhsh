<?php

namespace backend\models\searches;

use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\GroupActive;

/**
 * GroupActiveSearch represents the model behind the search form of `common\models\GroupActive`.
 */
class GroupActiveSearch extends GroupActive
{
    public $goods_id;
    public $goodsOptions= [];
    public $start_time;
    public $end_time;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'schedule_id', 'continued', 'status', 'operator_id', 'company_id', 'owner_type','goods_id'], 'integer'],
            [['active_no', 'rule_desc', 'created_at', 'updated_at','start_time','end_time'], 'safe'],
        ];
    }

    public function attributeLabels(){
        return array_merge(parent::attributeLabels(),[
            'owner_type'=>'归属',
            'goods_id' => '商品ID',
            'start_time' => '开始时间',
            'end_time' => '结束时间'
        ]);
    }

    /**
     * {@inheritdoc}
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
        $groupActiveTable = GroupActive::tableName();
        $scheduleTable = GoodsSchedule::tableName();
        $query = GroupActive::find();

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
            "{$groupActiveTable}.id" => $this->id,
            "{$groupActiveTable}.schedule_id" => $this->schedule_id,
            "{$groupActiveTable}.continued" => $this->continued,
            "{$groupActiveTable}.status" => $this->status,
            "{$groupActiveTable}.operator_id" => $this->operator_id,
            "{$groupActiveTable}.company_id" => $this->company_id,
            "{$groupActiveTable}.owner_type" => $this->owner_type,
            "{$groupActiveTable}.created_at" => $this->created_at,
            "{$groupActiveTable}.updated_at" => $this->updated_at,
        ]);
        $query->andFilterWhere([
            "{$groupActiveTable}.status" => array_keys(GroupActive::$activeStatus),
        ]);
        $query->andFilterWhere(['like', "{$groupActiveTable}.active_no", $this->active_no])
            ->andFilterWhere(['like', "{$groupActiveTable}.rule_desc", $this->rule_desc]);

        if (StringUtils::isNotBlank($this->goods_id)||StringUtils::isNotBlank($this->start_time)||StringUtils::isNotBlank($this->end_time)){
            $query->leftJoin($scheduleTable,"{$groupActiveTable}.schedule_id = {$scheduleTable}.id");
            if (StringUtils::isNotBlank($this->goods_id)){
                $query->andFilterWhere([
                    "{$scheduleTable}.goods_id" => $this->goods_id,
                ]);
            }
            if (StringUtils::isNotBlank($this->start_time)){
                $query->andFilterWhere([
                    '>=',"{$scheduleTable}.online_time",$this->start_time,
                ]);
            }
            if (StringUtils::isNotBlank($this->end_time)){
                $query->andFilterWhere([
                    '<=',"{$scheduleTable}.online_time",$this->end_time,
                ]);
            }
        }
        $query->orderBy("{$groupActiveTable}.updated_at desc");
        return $dataProvider;
    }
}
