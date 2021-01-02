<?php

namespace backend\models\searches;

use common\models\BonusBatchDrawLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BonusBatchDrawLogSearch represents the model behind the search form about `common\models\BonusBatchDrawLog`.
 */
class BonusBatchDrawLogSearch extends BonusBatchDrawLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'batch_id', 'draw_type', 'draw_type_id', 'num', 'operator_id', 'operator_type', 'biz_id'], 'integer'],
            [['remark', 'created_at', 'updated_at', 'operator_name', 'biz_type', 'biz_name'], 'safe'],
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
        $query = BonusBatchDrawLog::find();

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
            'batch_id' => $this->batch_id,
            'draw_type' => $this->draw_type,
            'draw_type_id' => $this->draw_type_id,
            'num' => $this->num,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'operator_id' => $this->operator_id,
            'operator_type' => $this->operator_type,
            'biz_id' => $this->biz_id,
        ]);

        $query->andFilterWhere(['like', 'remark', $this->remark])
            ->andFilterWhere(['like', 'operator_name', $this->operator_name])
            ->andFilterWhere(['like', 'biz_type', $this->biz_type]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
