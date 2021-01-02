<?php


namespace backend\controllers;


use backend\services\CustomerService;
use backend\utils\BRestfulResponse;
use yii\web\Controller;
use Yii;

class SearchOptionController extends  Controller
{


    /**
     * @return mixed|string
     */
    public function actionSearchCustomer() {
        $keyword = Yii::$app->request->get("keyword");
        try{
            $optionsArr = CustomerService::searchCustomerList($keyword);
            return BRestfulResponse::success($optionsArr);
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            return BRestfulResponse::success([]);
        }
    }

}