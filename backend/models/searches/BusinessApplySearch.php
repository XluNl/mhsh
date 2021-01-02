<?php

namespace backend\models\searches;

use common\models\BusinessApply;
use common\models\CommonStatus;
use common\models\UserInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BusinessApplySearch represents the model behind the search form about `common\models\BusinessApply`.
 */
class BusinessApplySearch extends BusinessApply
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'type', 'province_id', 'city_id', 'county_id', 'has_store', 'operator_id', 'action', 'status', 'company_id'], 'integer'],
            [['created_at', 'updated_at','nickname', 'realname', 'em_phone', 'wx_number', 'occupation', 'community', 'address', 'images', 'remark', 'invite_code', 'operator_name', 'operator_remark','head_img_url','phone'], 'safe'],
            [['lat', 'lng'], 'number'],
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
        $businessApplyTable = BusinessApply::tableName();
        $userInfoTable = UserInfo::tableName();
        $query = BusinessApply::find();

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
            "{$businessApplyTable}.id" => $this->id,
            "{$businessApplyTable}.created_at" => $this->created_at,
            "{$businessApplyTable}.updated_at" => $this->updated_at,
            "{$businessApplyTable}.user_id" => $this->user_id,
            "{$businessApplyTable}.type" => $this->type,
            "{$businessApplyTable}.province_id" => $this->province_id,
            "{$businessApplyTable}.city_id" => $this->city_id,
            "{$businessApplyTable}.county_id" => $this->county_id,
            "{$businessApplyTable}.lat" => $this->lat,
            "{$businessApplyTable}.lng" => $this->lng,
            "{$businessApplyTable}.has_store" => $this->has_store,
            "{$businessApplyTable}.operator_id" => $this->operator_id,
            "{$businessApplyTable}.action" => $this->action,
            "{$businessApplyTable}.status" => $this->status,
            "{$businessApplyTable}.company_id" => $this->company_id,
            "{$businessApplyTable}.head_img_url" => $this->head_img_url,

        ]);

        $query->andFilterWhere(['like', "{$businessApplyTable}.realname", $this->realname])
            ->andFilterWhere(['like', "{$businessApplyTable}.nickname", $this->nickname])
            ->andFilterWhere(['like', "{$businessApplyTable}.em_phone", $this->em_phone])
            ->andFilterWhere(['like', "{$businessApplyTable}.wx_number", $this->wx_number])
            ->andFilterWhere(['like', "{$businessApplyTable}.occupation", $this->occupation])
            ->andFilterWhere(['like', "{$businessApplyTable}.community", $this->community])
            ->andFilterWhere(['like', "{$businessApplyTable}.address", $this->address])
            ->andFilterWhere(['like', "{$businessApplyTable}.images", $this->images])
            ->andFilterWhere(['like', "{$businessApplyTable}.remark", $this->remark])
            ->andFilterWhere(['like', "{$businessApplyTable}.invite_code", $this->invite_code])
            ->andFilterWhere(['like', "{$businessApplyTable}.operator_name", $this->operator_name])
            ->andFilterWhere(['like', "{$businessApplyTable}.operator_remark", $this->operator_remark]);
        $query->leftJoin($userInfoTable,"{$userInfoTable}.id={$businessApplyTable}.user_id");
        $query->andFilterWhere([
            "{$businessApplyTable}.status" => CommonStatus::STATUS_ACTIVE,
            "{$userInfoTable}.phone" =>$this->phone
        ]);
        $query->select("{$businessApplyTable}.*,{$userInfoTable}.phone");
        $query->orderBy("id desc");
        return $dataProvider;
    }
}
