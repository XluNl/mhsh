<?php

namespace backend\models\searches;

use Yii;
use common\models\ApiLog;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%api_log}}".
 *
 * @property int $id
 * @property string|null $ip
 * @property string|null $app_id
 * @property string|null $module
 * @property string|null $controller
 * @property string|null $action
 * @property string|null $request
 * @property string|null $response
 * @property int|null $created_at
 */
class ApiLogSearch extends ApiLog
{

     public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $_GET['per-page'] ?? Yii::$app->params['pageSize'] ?? 15,
            ],
        ]);
        $this->load($params);
        if(empty($this->env)){
            $this->env = self::TYPE_DEV;
        }

        $query->andFilterWhere([
            'app_id' => trim($this->app_id),
            'module' => trim($this->module),
            'controller' => trim($this->controller),
            'action' => trim($this->action),
            'env' => self::$typeArr[$this->env]
        ]);

        $query->orderBy('id desc');

        // $query = $query->createCommand()->getRawSql();
        // var_dump($query);die;

        return $dataProvider;
    }

    public static function delExp($params){
        if($params['tag'] ==1 ){
            $exp_time = strtotime(date("Y-m-d")."- 3 day");
        }else{
            $exp_time = strtotime(date("Y-m-d"));
        }
        $conditions = ['and',['<','created_at',$exp_time]];
        $env = $params['query']['ApiLogSearch']['env'];
        if($env){
            $conditions[] = ['env'=>self::$typeArr[$env]];
        }
        self::deleteAll($conditions);
    }

    public function add($data){
        $l_d['ApiLog'] =$data;
        if($this->load($l_d) && $this->save()){
            return true;
        }
        return false;
    }
}
