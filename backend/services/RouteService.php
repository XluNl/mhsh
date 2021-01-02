<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\Route;
use common\models\RouteDelivery;
use common\utils\ArrayUtils;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class RouteService extends \common\services\RouteService
{
    /**
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException RedirectParams
     * @throws \yii\db\Exception
     */
    public static function operate($id,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,Route::$statusArr),$validateException);
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $count = Route::updateAll(['status'=>$commander],['id'=>$id,'company_id'=>$company_id]);
            BExceptionAssert::assertTrue($count>0,BBusinessException::create("状态更新失败"));
            if ($commander==Route::STATUS_DELETED){
                RouteDelivery::deleteAll(['company_id'=>$company_id,'route_id'=>$id]);
            }
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }

    }

    /**
     * 获取 校验非空
     * @param $id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|Route|\yii\db\ActiveRecord|null
     */
    public static function requireModel($id, $company_id, $validateException, $model = false){
        $model = self::getActiveModel($id,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 路线列表
     * @param $company_id
     * @return array
     */
    public static function getRouteList($company_id){
        $routeDeliveryTable = RouteDelivery::tableName();
        $deliveryTable = Delivery::tableName();
        $routes = self::getActiveByCompanyId($company_id);
        $routesCount = (new Query())->from($routeDeliveryTable)
            ->leftJoin($deliveryTable,
                "{$routeDeliveryTable}.delivery_id={$deliveryTable}.id")
            ->select(['COUNT(*) AS count','route_id'])
            ->where([
                "{$routeDeliveryTable}.company_id"=>$company_id,
                "{$deliveryTable}.status"=>CommonStatus::STATUS_ACTIVE,
            ])
            ->groupBy("route_id")
            ->all();
        $routesCount = ArrayUtils::index($routesCount,'route_id');

        $deliveryCount = (new Query())->from(Delivery::tableName())
            ->where(['company_id'=>$company_id,'status'=>CommonStatus::STATUS_ACTIVE])->count();
        $assignDeliveryCount = 0;
        foreach ($routes as $k=>$v){
            if (key_exists($v['id'],$routesCount)){
                $v['delivery_count'] = $routesCount[$v['id']]['count'];
                $assignDeliveryCount +=$v['delivery_count'];
            }
            else{
                $v['delivery_count'] =0;
            }
            $routes[$k] = $v;
        }
        $routes = array_merge([
            ['id'=>'-1','nickname'=>'所有团长','phone'=>'#','delivery_count'=>$deliveryCount],
            ['id'=>'0','nickname'=>'未分配团长','phone'=>'#','delivery_count'=>$deliveryCount-$assignDeliveryCount]
        ],$routes);
        return $routes;

    }

    /**
     * 按路线id查找配送点
     * @param $routeId
     * @param $companyId
     * @return array
     */
    public static function getDeliveryByRouteId($routeId,$companyId){
        $routeDeliveryTable = RouteDelivery::tableName();
        $deliveryTable = Delivery::tableName();
        if ($routeId==-1){
            $conditions = ['and',['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE]];
            $model = (new Query())->from($deliveryTable)->where($conditions)->all();
            return $model;
        }
        else if ($routeId==0){
            $routeDeliveryModels = (new Query())->from($routeDeliveryTable)->where(['company_id'=>$companyId])->all();
            $patchIds = [];
            if (!empty($routeDeliveryModels)){
                $patchIds = ArrayHelper::getColumn($routeDeliveryModels,'delivery_id');
            }
            $conditions = ['and',['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE]];
            if (!empty($patchIds)){
                $conditions[] = ['not in','id',$patchIds];
            }
            $model = (new Query())->from($deliveryTable)->where($conditions)->all();
            return $model;
        }
        else{
            $model = (new Query())->from($routeDeliveryTable)->leftJoin($deliveryTable,
                "{$routeDeliveryTable}.delivery_id={$deliveryTable}.id")
                ->where([
                    "{$routeDeliveryTable}.company_id"=>$companyId,
                    "{$routeDeliveryTable}.route_id"=>$routeId,
                    "{$deliveryTable}.status"=>CommonStatus::STATUS_ACTIVE,
                ])->all();
            return $model;
        }
    }

    /**
     * 配送点分配线路
     * @param $routeId
     * @param $deliveryId
     * @param $companyId
     * @throws \yii\db\StaleObjectException
     */
    public static function updateRouteDelivery($routeId,$deliveryId,$companyId){
        $routeDeliveryModel = parent::getRouteDeliveryModel($deliveryId,$companyId,true);
        if (empty($routeDeliveryModel)){
            if ($routeId!=0){
                $routeDeliveryModel = new RouteDelivery();
                $routeDeliveryModel->company_id = $companyId;
                $routeDeliveryModel->route_id = $routeId;
                $routeDeliveryModel->delivery_id = $deliveryId;
                BExceptionAssert::assertTrue($routeDeliveryModel->save(),BBusinessException::create("分配失败"));
            }
        }
        else{
            if ($routeId==0){
                BExceptionAssert::assertTrue($routeDeliveryModel->delete()!=false,BBusinessException::create("分配失败"));
            }
            else{
                $routeDeliveryModel->route_id = $routeId;
                BExceptionAssert::assertTrue($routeDeliveryModel->save(),BBusinessException::create("分配失败"));
            }
        }
    }

}