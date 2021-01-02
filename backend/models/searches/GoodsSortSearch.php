<?php

namespace backend\models\searches;

use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\GoodsSort;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GoodsSortSearch represents the model behind the search form about `common\models\GoodsSort`.
 */
class GoodsSortSearch extends GoodsSort
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sort_order', 'parent_id', 'sort_show', 'sort_status', 'company_id', 'sort_owner'], 'integer'],
            [['sort_name', 'pic_name', 'created_at', 'updated_at'], 'safe'],
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
        $query = GoodsSort::find();

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

        if (StringUtils::isBlank($this->sort_owner)){
            $this->sort_owner = GoodsConstantEnum::OWNER_SELF;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'sort_order' => $this->sort_order,
            'parent_id' => 0,
            'sort_show' => $this->sort_show,
            'sort_status' => $this->sort_status,
            'company_id' => $this->company_id,
            'sort_owner' => $this->sort_owner,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'sort_name', trim($this->sort_name)])
            ->andFilterWhere(['like', 'pic_name', $this->pic_name]);
        $query->andFilterWhere(['sort_status'=>CommonStatus::STATUS_ACTIVE]);
        $query->with(['subSort']);
        $query->orderBy("sort_order desc");
        return $dataProvider;
    }
}
