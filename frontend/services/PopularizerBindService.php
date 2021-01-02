<?php


namespace frontend\services;


use common\models\PopularizerBind;
use common\utils\DateTimeUtils;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;

class PopularizerBindService
{
    /**
     * 绑定分享关系
     * @param $customerId
     * @param $popularizerId
     * @param $company_id
     */
    public static function bind($customerId,$popularizerId,$company_id){
        $popularizer = PopularizerService::getActiveModel($popularizerId,$company_id);
        ExceptionAssert::assertNotNull($popularizer,StatusCode::createExpWithParams(StatusCode::POPULARIZER_BIND_ERROR,'分享者未在此代理商下注册'));
        $model = new PopularizerBind();
        $model->customer_id = $customerId;
        $model->company_id = $company_id;
        $model->popularizer_id = $popularizerId;
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::POPULARIZER_BIND_ERROR,'保存失败'));
    }

    public static function queryPopularizerRelative($company_id,$customerId){
        $res = (new Query())->from(PopularizerBind::tableName())->where([
            'and',
            ['customer_id'=>$customerId,
                'company_id'=>$company_id,],
            ['>=','created_at',self::timeLimit()]
        ])->orderBy('created_at desc')->limit(1)->all();
        return empty($res)?null:$res[0]['popularizer_id'];
    }
    private static function timeLimit(){
        return DateTimeUtils::parseStandardWLongDate(time()-7*24*3600);
    }
}