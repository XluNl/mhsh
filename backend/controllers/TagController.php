<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\searches\TagSearch;
use backend\services\TagService;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use Yii;
use yii\web\Controller;

/**
 * TagController implements the CRUD actions for Tag model.
 */
class TagController extends Controller
{

    /**
     * Lists all Tag models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TagSearch();
        BackendCommon::addCompanyIdToParams('TagSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $searchModel->tagOptions = TagService::getOptionsByGroupId($searchModel->group_id);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionOptions() {
        $groupId = Yii::$app->request->get("group_id",null);
        BExceptionAssert::assertNotBlank($groupId,BBusinessException::create("group_id不能为空"));
        $optionsArr = TagService::getOptionsByGroupId($groupId);
        return BackendCommon::parseOptions($optionsArr);
    }
}
