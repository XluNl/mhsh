<?php

namespace backend\models\searches;

use common\models\AdminUser;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AdminUserSearch represents the model behind the search form about `common\models\AdminUser`.
 */
class AdminUserSearch extends AdminUser
{

    public $companyOptions = [];
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'role', 'status', 'confirmed_at', 'blocked_at', 'created_at', 'updated_at', 'flags'], 'integer'],
            [['username', 'email', 'password_hash', 'password_reset_token', 'auth_key', 'unconfirmed_email', 'registration_ip', 'nickname', 'is_super_admin', 'company_id'], 'safe'],
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
        $query = AdminUser::find();

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
            'role' => $this->role,
            'status' => $this->status,
            'confirmed_at' => $this->confirmed_at,
            'blocked_at' => $this->blocked_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'flags' => $this->flags,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'unconfirmed_email', $this->unconfirmed_email])
            ->andFilterWhere(['like', 'registration_ip', $this->registration_ip])
            ->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'is_super_admin', $this->is_super_admin]);

        return $dataProvider;
    }
}
