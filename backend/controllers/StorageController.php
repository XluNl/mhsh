<?php
namespace backend\controllers;


class StorageController extends BaseController{

	public function actionSortingList()
    {
        return $this->render('index');
    }
}