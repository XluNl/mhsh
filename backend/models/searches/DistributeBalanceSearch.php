<?php

namespace backend\models\searches;

use backend\models\BackendCommon;
use common\models\Alliance;
use common\models\BizTypeEnum;
use common\models\Company;
use common\models\Customer;
use common\models\Delivery;
use common\models\DistributeBalance;
use common\models\Popularizer;
use common\utils\StringUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DistributeBalanceSearch represents the model behind the search form about `common\models\DistributeBalance`.
 */
class DistributeBalanceSearch extends DistributeBalance
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'user_id', 'biz_type', 'biz_id', 'amount', 'remain_amount', 'version'], 'integer'],
            [['created_at', 'updated_at','search_phone','search_name'], 'safe'],
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
        $distributeBalanceTable=DistributeBalance::tableName();
        $popularizerTable = Popularizer::tableName();
        $deliveryTable = Delivery::tableName();
        $allianceTable = Alliance::tableName();
        $companyTable = Company::tableName();
        $customerTable = Customer::tableName();

        $query = DistributeBalance::find();

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
            "{$distributeBalanceTable}.id" => $this->id,
            "{$distributeBalanceTable}.created_at" => $this->created_at,
            "{$distributeBalanceTable}.updated_at" => $this->updated_at,
            "{$distributeBalanceTable}.company_id" => $this->company_id,
            "{$distributeBalanceTable}.user_id" => $this->user_id,
            "{$distributeBalanceTable}.biz_type" => $this->biz_type,
            "{$distributeBalanceTable}.biz_id" => $this->biz_id,
            "{$distributeBalanceTable}.amount" => $this->amount,
            "{$distributeBalanceTable}.remain_amount" => $this->remain_amount,
            "{$distributeBalanceTable}.version" => $this->version,
        ]);
        $query->select(
            [
                "{$distributeBalanceTable}.*",
                "CASE 
                WHEN biz_type = 1 THEN
                    {$popularizerTable}.`realname`
                WHEN biz_type = 2 THEN
                    {$deliveryTable}.`realname`
                WHEN biz_type = 3 THEN
                    {$companyTable}.`name`
                WHEN biz_type = 4 THEN
                    {$allianceTable}.`nickname`
                WHEN biz_type in (5,6) THEN
                    {$customerTable}.`nickname`
                ELSE
                    ''	
                END AS search_name"
            ,
                "CASE 
                WHEN biz_type = 1 THEN
                    {$popularizerTable}.`phone`
                WHEN biz_type = 2 THEN
                    {$deliveryTable}.`phone`
                WHEN biz_type = 3 THEN
                    {$companyTable}.`telphone`
                WHEN biz_type = 4 THEN
                    {$allianceTable}.`phone`
                WHEN biz_type in (5,6) THEN
                    {$customerTable}.`phone`
                ELSE
                    ''	
                END AS search_phone"
            ]
        );
        $query->leftJoin($popularizerTable,"{$popularizerTable}.id={$distributeBalanceTable}.biz_id");
        $query->leftJoin($deliveryTable,"{$deliveryTable}.id={$distributeBalanceTable}.biz_id");
        $query->leftJoin($allianceTable,"{$allianceTable}.id={$distributeBalanceTable}.biz_id");
        $query->leftJoin($companyTable,"{$companyTable}.id={$distributeBalanceTable}.biz_id");
        $query->leftJoin($customerTable,"{$customerTable}.id={$distributeBalanceTable}.biz_id");
        if (!StringUtils::isBlank($this->search_name)){
            $query->andFilterWhere([
                'AND',
                "CASE 
                WHEN biz_type= 1 THEN
                    {$popularizerTable}.`realname` like '%{$this->search_name}%'
                WHEN biz_type=2 THEN
                    {$deliveryTable}.`realname`  like '%{$this->search_name}%'
                WHEN biz_type= 3 THEN
                    {$companyTable}.`name`  like '%{$this->search_name}%'
                WHEN biz_type= 4 THEN
                    {$allianceTable}.`realname`  like '%{$this->search_name}%'
                WHEN biz_type in(5,6) THEN
                    {$customerTable}.`nickname`  like '%{$this->search_name}%'    
                ELSE
                    '' = '{$this->search_name}'	
                END"]);
        }

        if (!StringUtils::isBlank($this->search_phone)){
            $query->andFilterWhere([
                'AND',
                "CASE
                WHEN biz_type= 1 THEN
                    {$popularizerTable}.`phone` like '%{$this->search_phone}%'
                WHEN biz_type= 2 THEN
                    {$deliveryTable}.`phone`  like '%{$this->search_phone}%'
                WHEN biz_type= 3 THEN
                    {$companyTable}.`telphone`  like '%{$this->search_phone}%'
                WHEN biz_type= 4 THEN
                    {$allianceTable}.`phone`  like '%{$this->search_phone}%'
                WHEN biz_type in(5,6) THEN
                    {$customerTable}.`phone`  like '%{$this->search_phone}%'
                ELSE
                    '' = '{$this->search_phone}'	
                END"]);
        }
        $query->andFilterWhere(['in',"{$distributeBalanceTable}.biz_type", BizTypeEnum::getBizTypeShowArrKey(BackendCommon::getFCompanyId())]);
        return $dataProvider;
    }
}
