<?php

namespace frontend\services;
use common\models\Cart;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CartOperationService{

	public $expire = 36000;

    public static function addGoods($userId, $schedule_id, $goods_num = 1) {
        $cart = Cart::findOne(['user_id'=>$userId,'schedule_id'=>$schedule_id]);
        if (empty($cart)){
            $cart = new Cart();
            $cart->schedule_id = $schedule_id;
            $cart->num = 0;
            $cart->user_id = $userId;
            $cart->is_check = Cart::CHECK;
        }
        $cart->num += $goods_num;
        ExceptionAssert::assertTrue($cart->save(),StatusCode::createExpWithParams(StatusCode::CART_OPERATION_ERROR,'添加失败'));
    }



    public static function delGoods($userId, $schedule_id, $goods_num = 1) {
        $cart = Cart::findOne(['user_id'=>$userId,'schedule_id'=>$schedule_id]);
        if (!empty($cart)){
            $cart->schedule_id = $schedule_id;
            $cart->num -= $goods_num;
            $cart->user_id = $userId;
            if ($cart->num<=0){
                ExceptionAssert::assertTrue($cart->delete(),StatusCode::createExpWithParams(StatusCode::CART_OPERATION_ERROR,'删除失败'));
            }
            else{
                ExceptionAssert::assertTrue($cart->save(),StatusCode::createExpWithParams(StatusCode::CART_OPERATION_ERROR,'删除失败'));
            }
        }
    }

    public static function modifyGoods($userId,$schedule_id, $goods_num = 1) {
        if ($goods_num<=0){
            Cart::deleteAll(['user_id'=>$userId,'schedule_id'=>$schedule_id]);
        }
        else {
            $cart = Cart::findOne(['user_id'=>$userId,'schedule_id'=>$schedule_id]);
            if (empty($cart)){
                $cart = new Cart();
                $cart->schedule_id = $schedule_id;
                $cart->user_id = $userId;
            }
            $cart->num = $goods_num;
            ExceptionAssert::assertTrue($cart->save(),StatusCode::createExpWithParams(StatusCode::CART_OPERATION_ERROR,'固值失败'));
        }
    }

    public static function isEmpty($userId) {
        $count = Cart::find()->where(['user_id'=>$userId])->count();
        if (empty($count)){
            return true;
        }
        else{
            return false;
        }
    }

    public static function count($userId) {
        $count = Cart::find()->where(['user_id'=>$userId])->count();
        if (empty($count)){
            return 0;
        }
        return $count;
    }

    public static function emptyGoods($userId) {
        Cart::deleteAll(['user_id'=>$userId]);
    }


    public static function listGoods($userId) {
        $carts = Cart::find()->where(['user_id'=>$userId])->asArray()->all();
        if (empty($carts)){
            return [];
        }
        else{
            $goodsList = [];
            foreach ($carts as $cart){
                $goodsList[$cart['schedule_id']]=$cart['num'];
            }
            return $goodsList;
        }
    }


    /**
     * 购物车汇总
     * @param $userId
     * @param $companyId
     * @param $deliveryId
     * @return array
     */
    public static function summaryCart($userId,$companyId,$deliveryId){
        $scheduleLists = CartOperationService::listGoodsWithCheck($userId);
        foreach ($scheduleLists as $k=>$v){
            if ($v['is_check']==Cart::UNCHECK){
                unset($scheduleLists[$k]);
            }
        }
        $ownerTypeCountArray = self::getZeroDetail();
        $total = 0;
        if (!empty($scheduleLists)){
            $scheduleIds = array_keys($scheduleLists);
            $scheduleModels = GoodsScheduleService::getDisplayUpByIds(null,$companyId,$scheduleIds,null,$deliveryId);
            $scheduleModels = ArrayUtils::index($scheduleModels,'schedule_id');
            foreach ($scheduleModels as $value){
                if (!key_exists($value['goods_owner'],$ownerTypeCountArray)){
                    $ownerTypeCountArray[$value['goods_owner']] = 0;
                }
                $ownerTypeCountArray[$value['goods_owner']]+=$scheduleLists[$value['schedule_id']]['num'];
                $total+=$scheduleLists[$value['schedule_id']]['num'];
            }
        }
        $ownerTypeCount = ArrayUtils::mapToArray($ownerTypeCountArray,'ownerType','num');
        return [
            'detail'=>$ownerTypeCount,
            'total'=>$total
        ];
    }

    public static function listGoodsWithCheck($userId) {
        $carts = Cart::find()->where(['user_id'=>$userId])->asArray()->all();
        if (empty($carts)){
            return [];
        }
        else{
            $goodsList = [];
            foreach ($carts as $cart){
                $goodsList[$cart['schedule_id']]=['schedule_id'=>$cart['schedule_id'],'num'=>$cart['num'],'is_check'=>$cart['is_check']];
            }
            return $goodsList;
        }
    }


    public static function listCheckGoods($userId) {
        $carts = Cart::find()->where(['user_id'=>$userId,'is_check'=>Cart::CHECK])->asArray()->all();
        if (empty($carts)){
            return [];
        }
        else{
            $goodsList = [];
            foreach ($carts as $cart){
                $goodsList[$cart['schedule_id']]=$cart['num'];
            }
            return $goodsList;
        }
    }


    public static function check($userId, $scheduleId,$ownerType=null) {
        $conditions = ['user_id'=>$userId];
        if (StringUtils::isNotBlank($scheduleId)){
            $conditions['schedule_id'] = $scheduleId;
        }
        if (StringUtils::isNotBlank($ownerType)){
            $goodsTable = Goods::tableName();
            $scheduleTable = GoodsSchedule::tableName();
            $cartTable = Cart::tableName();
            $scheduleIds = (new Query())->from($cartTable)
                ->select("{$cartTable}.schedule_id")
                ->leftJoin($scheduleTable,"{$cartTable}.schedule_id={$scheduleTable}.id")
                ->leftJoin($goodsTable,"{$goodsTable}.id={$scheduleTable}.goods_id")
                ->where(["{$goodsTable}.owner_type"=>$ownerType]);
            if (empty($scheduleIds)){
                return;
            }
            $conditions['schedule_id'] = $scheduleIds;
        }
        Cart::updateAll(['is_check'=>Cart::CHECK],$conditions);

    }

    public static function unCheck($userId, $scheduleId,$ownerType=null) {
        $conditions = ['user_id'=>$userId];
        if (StringUtils::isNotBlank($scheduleId)){
            $conditions['schedule_id'] = $scheduleId;
        }
        if (StringUtils::isNotBlank($ownerType)){
            $goodsTable = Goods::tableName();
            $scheduleTable = GoodsSchedule::tableName();
            $cartTable = Cart::tableName();
            $scheduleIds = (new Query())->from($cartTable)
                ->select("{$cartTable}.schedule_id")
                ->leftJoin($scheduleTable,"{$cartTable}.schedule_id={$scheduleTable}.id")
                ->leftJoin($goodsTable,"{$goodsTable}.id={$scheduleTable}.goods_id")
                ->where(["{$goodsTable}.owner_type"=>$ownerType]);
            if (empty($scheduleIds)){
                return;
            }
            $conditions['schedule_id'] = $scheduleIds;
        }
        Cart::updateAll(['is_check'=>Cart::UNCHECK],$conditions);
    }


    public static function getZeroDetail(){
        $ownerTypeCountArray = [];
        foreach (GoodsConstantEnum::$ownerArr as  $k=>$v){
            $ownerTypeCountArray[$k] = 0;
        }
        return $ownerTypeCountArray;
    }
}
