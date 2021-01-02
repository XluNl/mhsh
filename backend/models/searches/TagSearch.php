<?php

namespace backend\models\searches;

use common\models\Tag;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TagSearch represents the model behind the search form about `common\models\Tag`.
 */
class TagSearch extends Tag
{

    public $tagOptions = [];
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'group_id', 'tag_id'], 'integer'],
            [['created_at', 'updated_at', 'biz_id', 'biz_name', 'biz_ext', 'start_time', 'end_time'], 'safe'],
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
        $query = Tag::find();

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
            'company_id' => $this->company_id,
            'group_id' => $this->group_id,
            'tag_id' => $this->tag_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        $query->andFilterWhere(['like', 'biz_id', $this->biz_id])
            ->andFilterWhere(['like', 'biz_name', $this->biz_name])
            ->andFilterWhere(['like', 'biz_ext', $this->biz_ext]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
