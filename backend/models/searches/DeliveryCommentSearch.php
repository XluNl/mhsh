<?php

namespace backend\models\searches;

use common\models\DeliveryComment;
use common\models\Goods;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DeliveryCommentSearch represents the model behind the search form about `common\models\DeliveryComment`.
 */
class DeliveryCommentSearch extends DeliveryComment
{
    public $goods_name;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'delivery_id', 'goods_id', 'sku_id', 'status', 'is_show', 'operator_id'], 'integer'],
            [['created_at', 'updated_at', 'images', 'content', 'operator_name'], 'safe'],
        ];
    }

    public function attributeLabels(){
        return array_merge(parent::attributeLabels(),[
            'goods_name'=>'商品名称',
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
        $deliveryCommentTable = DeliveryComment::tableName();
        $goodsTable = Goods::tableName();
        $query = DeliveryComment::find();

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
            "{$deliveryCommentTable}.id" => $this->id,
            "{$deliveryCommentTable}.created_at" => $this->created_at,
            "{$deliveryCommentTable}.updated_at" => $this->updated_at,
            "{$deliveryCommentTable}.company_id" => $this->company_id,
            "{$deliveryCommentTable}.delivery_id" => $this->delivery_id,
            "{$deliveryCommentTable}.goods_id" => $this->goods_id,
            "{$deliveryCommentTable}.sku_id" => $this->sku_id,
            "{$deliveryCommentTable}.status" => $this->status,
            "{$deliveryCommentTable}.is_show" => $this->is_show,
            "{$deliveryCommentTable}.operator_id" => $this->operator_id,
        ]);
        $query->andFilterWhere(["{$deliveryCommentTable}.status" => [DeliveryComment::STATUS_APPLY,DeliveryComment::STATUS_ACCEPT,DeliveryComment::STATUS_DENY]]);
        $query->andFilterWhere(['like', "{$deliveryCommentTable}.images", $this->images])
            ->andFilterWhere(['like', "{$deliveryCommentTable}.content", $this->content])
            ->andFilterWhere(['like', "{$deliveryCommentTable}.operator_name", $this->operator_name]);
        $query->leftJoin($goodsTable,"{$deliveryCommentTable}.goods_id={$goodsTable}.id");
        $query->andFilterWhere(['like', "{$goodsTable}.goods_name", $this->goods_name]);
        $query->with(['goods','goodsSku','delivery']);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
