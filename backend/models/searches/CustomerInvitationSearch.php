<?php

namespace backend\models\searches;

use common\models\Customer;
use common\models\CustomerInvitation;
use common\models\UserInfo;
use common\utils\DateTimeUtils;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CustomerInvitationSearch represents the model behind the search form about `common\models\CustomerInvitation`.
 */
class CustomerInvitationSearch extends CustomerInvitation
{
    public $phone;
    public $start_time;
    public $end_time;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'parent_id', 'status'], 'integer'],
            [['created_at', 'updated_at','phone','start_time','end_time'], 'safe'],
        ];
    }

    public function attributeLabels(){
        return array_merge(parent::attributeLabels(),[
            'phone'=>'一级联系人手机号',
            'start_time'=>'起始时间',
            'end_time'=>'截止时间',
        ]);
    }

    public function getOneLevelInvitationDetail(){
        $startTime=1;$endTime=1;
        return [$startTime,$endTime];
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
        $customerInvitationTable = CustomerInvitation::tableName();
        $customerTable = Customer::tableName();
        $userInfoTable = UserInfo::tableName();
        $query = CustomerInvitation::find();

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
            "{$customerInvitationTable}.id" => $this->id,
            "{$customerInvitationTable}.created_at" => $this->created_at,
            "{$customerInvitationTable}.updated_at" => $this->updated_at,
            "{$customerInvitationTable}.customer_id" => $this->customer_id,
            "{$customerInvitationTable}.parent_id" => $this->parent_id,
            "{$customerInvitationTable}.status" => $this->status,
        ]);
        $query->innerJoin($customerTable,"{$customerTable}.id={$customerInvitationTable}.customer_id");
        $query->innerJoin($userInfoTable,"{$userInfoTable}.id={$customerTable}.user_id");
        $query->andFilterWhere(['like', "{$userInfoTable}.phone", $this->phone]);
        $query->select("{$customerInvitationTable}.*,{$customerTable}.*,{$userInfoTable}.*");
        $query->asArray();
        return $dataProvider;
    }
}
