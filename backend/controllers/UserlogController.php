<?php
namespace backend\controllers;

use common\models\AdminUserLog;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;

/**
 * Userlog Controller
 */
class UserlogController extends Controller
{
    

    /**
     * @inheritdoc
     */
 

    public function actionIndex()
    {
        return $this->redirect('list');
    }
    
    public function actionList()
    {
        $start_time = Yii::$app->request->get("start_time");
        $end_time = Yii::$app->request->get("end_time");
        
        $user_id = Yii::$app->user->identity->id;
        $query = AdminUserLog::find()->where(array('user_id'=>$user_id));
        if (!empty($start_time)){
            $query->andWhere(['>=','create_time',$start_time]);
        }
        if (!empty($end_time)){
            $query->andWhere(['<=','create_time',$end_time]);
        }
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->pageSize = 25;
        $models = $query->offset($pages->offset)
        ->limit($pages->limit)->orderBy('create_time desc')
        ->all();
        if (empty($start_time)){
            $start_time = date('Y-m-d H:i',time()-3600*24*30);
        }
        if (empty($end_time)){
            $end_time = date('Y-m-d H:i',time());
        }
        return $this->render('list', array('models' => $models, 'pages' => $pages,'start_time'=>$start_time,'end_time'=>$end_time));
    }

    
}
