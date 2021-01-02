<?php

namespace backend\models\searches;

use backend\models\BackendCommon;
use common\models\BizTypeEnum;
use common\models\WithdrawApply;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * WithdrawApplySearch represents the model behind the search form about `common\models\WithdrawApply`.
 */
class WithdrawApplySearch extends WithdrawApply
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'biz_type', 'biz_id', 'audit_status', 'amount', 'type', 'process_status', 'version', 'is_return'], 'integer'],
            [['created_at', 'updated_at', 'biz_name'], 'safe'],
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
        $query = WithdrawApply::find();

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
            'biz_type' => $this->biz_type,
            'biz_id' => $this->biz_id,
            'audit_status' => $this->audit_status,
            'amount' => $this->amount,
            'type' => $this->type,
            'process_status' => $this->process_status,
            'version' => $this->version,
            'is_return' => $this->is_return,
        ]);

        $query->andFilterWhere(['like', 'biz_name', $this->biz_name]);
        $query->andFilterWhere(['in',"biz_type", BizTypeEnum::getBizTypeShowArrKey(BackendCommon::getFCompanyId())]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
