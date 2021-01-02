<?php
namespace backend\controllers;

use backend\models\BackendCommon;
use common\models\Region;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * RegionController
 */
class RegionController extends BaseController
{


    /*public function actions()
    {
        $actions=parent::actions();
        $actions['get-region']=[
            'class'=>RegionAction::className(),
            'model'=>Region::className()
        ];
        return $actions;
    }

    public function actionInx() {

        $p1 = Region::find()->where(['level'=>0])->all();
        $json = [];
        foreach ($p1 as $k1 =>$v1){
            $t1 = ['id'=>$v1['id'],'name'=>$v1['name']];
            $child1 = [];
            $p2 = Region::find()->where(['level'=>1,'parent_id'=>$v1['id']])->all();
            foreach ($p2 as $k2 =>$v2){
                $t2 = ['id'=>$v2['id'],'name'=>$v2['name']];
                $child2 = [];
                $p3 = Region::find()->where(['level'=>2,'parent_id'=>$v2['id']])->all();
                foreach ($p3 as $k3 =>$v3){
                    $t3 = ['id'=>$v3['id'],'name'=>$v3['name']];
                    $child2[] = $t3;
                }
                $t2['child'] = $child2;
                $child1[] = $t2;
            }
            $t1['child'] = $child1;
            $json[] = $t1;
        }
        echo json_encode($json);
    }*/

    public function actionRegion(){
        $pid = $id = \Yii::$app->request->get("id",0);
        $models = (new Query())->from(Region::tableName())->where(['parent_id'=>$pid])->select('id,name')->all();
        if (empty($models)){
            $models =[];
        }
        else{
            $models = ArrayHelper::map($models,'id','name');
        }
        return BackendCommon::parseOptions($models);
    }


 
}
