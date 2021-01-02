<?php


namespace business\services;
use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\BizTypeEnum;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderPreDistribute;
use common\models\Delivery;
use frontend\services\OrderDisplayDomainService;

class PreDistributeService
{
    /**
     * 预分润订单详情
     * @param $bizType
     * @param $bizId
     * @param int $pageNo
     * @param int $pageSize
     * @return array|\common\models\DistributeBalanceItem[]|Order[]|\yii\db\ActiveRecord[]
     */
    public static function preDistributeOrder($bizType,$userId,$isOuth,$pageNo=1,$pageSize=20,$start_time=null,$end_time=null){
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_POPULARIZER,BizTypeEnum::BIZ_TYPE_DELIVERY]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'未知类型'));
        $orders = [];
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        if ($bizType==BizTypeEnum::BIZ_TYPE_POPULARIZER){
            $orders = self::preDistributeOrderForPopularizer($bizId,$pageNo,$pageSize);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_DELIVERY){
            $orders = self::preDistributeOrderForDelivery($bizId,$isOuth,$pageNo,$pageSize,$start_time,$end_time);
        }
        $orders = OrderDisplayDomainService::batchDefineOrderDisplayData($orders);
        $reData['list'] = $orders;
        $reData['summary'] = self::preCommissionStatistics($bizId,$isOuth,$start_time,$end_time);
        return $reData;
    }

    /**
     * 预分润订单详情 (分享团长)
     * @param $popularizerId
     * @param int $pageNo
     * @param int $pageSize
     * @return array|\common\models\DistributeBalanceItem[]|Order[]|\yii\db\ActiveRecord[]
     */
    public static function preDistributeOrderForPopularizer($popularizerId,$pageNo=1,$pageSize=20){
        $orders = Order::find()->where([
            'and',
            ['or',['share_rate_id_1'=>$popularizerId],['share_rate_id_2'=>$popularizerId]],
            ['order_owner'=>[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_DELIVERY]],
            ['order_status'=>Order::$activeStatusArr]
        ])
            ->with(['goods','preDistributes'])
            ->offset($pageSize*($pageNo-1))->limit($pageSize)
            ->orderBy('created_at desc')
            ->asArray()
            ->all();
        foreach ($orders as $k=>$v){
            $preDistributeDetail = [
                'one_level_amount' => 0,
                'two_level_amount' => 0,
                'biz_owner_amount' => 0,
            ];
            if (key_exists('preDistributes',$v)&&!empty($v['preDistributes'])){
                $preDistributes = $v['preDistributes'];
                foreach ($preDistributes as $kk=>$vv){
                    if ($vv['biz_type']==OrderPreDistribute::BIZ_TYPE_CUSTOMER&&$vv['level']==OrderPreDistribute::LEVEL_ONE){
                        $preDistributeDetail['one_level_amount'] = $vv['amount'];
                    }
                    if ($vv['biz_type']==OrderPreDistribute::BIZ_TYPE_CUSTOMER&&$vv['level']==OrderPreDistribute::LEVEL_TWO){
                        $preDistributeDetail['two_level_amount'] = $vv['amount'];
                    }
                    if ($vv['biz_type']==OrderPreDistribute::BIZ_TYPE_POPULARIZER&&$vv['biz_id']==$popularizerId){
                        $preDistributeDetail['biz_owner_amount'] = $vv['amount'];
                    }
                }
            }
            $v['preDistributes'] = $preDistributeDetail;
            $orders[$k] = $v;
        }
        return $orders;
    }

    /**
     * 预分润订单详情 (配送团长,区分升级合伙人)
     * @param $deliveryId
     * @param int $pageNo
     * @param int $pageSize
     * @return array|\common\models\DistributeBalanceItem[]|Order[]|\yii\db\ActiveRecord[]
     */
    public static function preDistributeOrderForDelivery($deliveryId,$isOuth,$pageNo=1,$pageSize=20,$start_time=null,$end_time=null)
    {
        ExceptionAssert::assertNotNull(in_array($isOuth,[Delivery::AUTH_STATUS_NO_AUTH,Delivery::AUTH_STATUS_AUTH]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'非法授权类型'));
        
        $condition = ['delivery_id'=>$deliveryId,'order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_SELF];
        if($isOuth == Delivery::AUTH_STATUS_AUTH){
            $condition['order_owner'] = GoodsConstantEnum::OWNER_DELIVERY;
        }
        
        $query = Order::find()->where($condition)
            ->with(['goods','preDistributes','customer.user'])
            ->offset($pageSize*($pageNo-1))->limit($pageSize)
            ->orderBy('created_at desc');
        
        if($start_time && $end_time){ 
            $query->andWhere(['and',['>=','created_at',$start_time],['<=','created_at',$end_time]]);
        }else{
            $query->andWhere("TO_DAYS(created_at)=TO_DAYS('$end_time')");
        }

        $orders = $query->asArray()->all();
        foreach ($orders as $k=>$v){
            $preDistributeDetail = [
                'one_level_amount' => 0,
                'two_level_amount' => 0,
                'biz_owner_amount' => 0,
            ];
            if (key_exists('preDistributes',$v)&&!empty($v['preDistributes'])){
                $preDistributes = $v['preDistributes'];
                foreach ($preDistributes as $kk=>$vv){
                    if ($vv['biz_type']==OrderPreDistribute::BIZ_TYPE_CUSTOMER&&$vv['level']==OrderPreDistribute::LEVEL_ONE){
                        $preDistributeDetail['one_level_amount'] = $vv['amount'];
                    }
                    if ($vv['biz_type']==OrderPreDistribute::BIZ_TYPE_CUSTOMER&&$vv['level']==OrderPreDistribute::LEVEL_TWO){
                        $preDistributeDetail['two_level_amount'] = $vv['amount'];
                    }
                    if ($vv['biz_type']==OrderPreDistribute::BIZ_TYPE_DELIVERY){
                        $preDistributeDetail['biz_owner_amount'] = $vv['amount'];
                    }
                }
            }
            $v['preDistributes'] = $preDistributeDetail;
            $orders[$k] = $v;
        }
        return $orders;
    }

    /**
     * [preTransactionAmont 团销售总金额及佣金总额,区分升级合伙人]
     * @param  [type] $deliveryId [description]
     * @param  [type] $start_time [description]
     * @param  [type] $end_time   [description]
     * @return [type]             [description]
     */
    public static function preCommissionStatistics($deliveryId,$isOuth,$start_time,$end_time)
    {   
        ExceptionAssert::assertNotNull(in_array($isOuth,[Delivery::AUTH_STATUS_NO_AUTH,Delivery::AUTH_STATUS_AUTH]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'非法授权类型'));
        $condition = ['delivery_id'=>$deliveryId,'order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_SELF];
        if($isOuth == Delivery::AUTH_STATUS_AUTH){
            $condition['order_owner'] = GoodsConstantEnum::OWNER_DELIVERY;
        }
        $query = Order::find()->where($condition)
            ->with(['goods','preDistributes'])
            ->orderBy('created_at desc');
        if($start_time && $end_time){ 
            $query->andWhere(['and',['>=','created_at',$start_time],['<=','created_at',$end_time]]);
        }else{
            $query->andWhere("TO_DAYS(created_at)=TO_DAYS('$end_time')");
        }
        $reData = [
            'totAmount'=>0,
            'sellAmount' => 0
        ];
        foreach ($query->each(50) as $key => $order) {
            $preDistributes = $order['preDistributes'];
            $reData['totAmount'] += $order['pay_amount'];
            foreach ($preDistributes as $kk => $vv) {
                if ($vv['biz_type']==OrderPreDistribute::BIZ_TYPE_DELIVERY){
                    $reData['sellAmount'] += $vv['amount'];
                }
            }
        }

        return $reData;

    }


    /**
     * [preSaleStatistics 团点销售统计]
     * @param  [type]  $bizType    [团点类型]
     * @param  [type]  $bizId      [团点id]
     * @param  integer $pageNo     [description]
     * @param  integer $pageSize   [description]
     * @param  [type]  $start_time [description]
     * @param  [type]  $end_time   [description]
     * @return [type]              [description]
     */
    public static function preSaleStatistics($bizType,$userId,$isOuth,$pageNo=1,$pageSize=20,$start_time=null,$end_time=null){
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_POPULARIZER,BizTypeEnum::BIZ_TYPE_DELIVERY]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'未知类型'));
        ExceptionAssert::assertNotNull(in_array($isOuth,[Delivery::AUTH_STATUS_NO_AUTH,Delivery::AUTH_STATUS_AUTH]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'非法授权类型'));
        $orders = [];
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        if ($bizType==BizTypeEnum::BIZ_TYPE_POPULARIZER){
            $orders = self::preDistributeSaleForPopularizer($bizId,$pageNo,$pageSize,$start_time,$end_time);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_DELIVERY){
            $orders = self::preDistributeSaleForDelivery($bizId,$isOuth,$pageNo,$pageSize,$start_time,$end_time);
        }

        foreach ($orders as $key => &$item) {
          $item['customer']  = OrderDisplayDomainService::defineCustomerOrder($item['customer']);
        }
        $reData['list'] = $orders;
        $reData['summary'] = self::preCommissionStatistics($bizId,$isOuth,$start_time,$end_time);
        return $reData;
    }


    /**
     * [preDistributeSaleForPopularizer 用户下单统计 (分享团点)]
     * @param  [type]  $deliveryId [description]
     * @param  integer $pageNo     [description]
     * @param  integer $pageSize   [description]
     * @param  [type]  $start_time [description]
     * @param  [type]  $end_time   [description]
     * @return [type]              [description]
     */
    public static function preDistributeSaleForPopularizer($deliveryId,$pageNo=1,$pageSize=20,$start_time=null,$end_time=null){

        return [];
    }



    /**
     * [preSalesStatistics 用户下单统计 (配送团点),区分升级合伙人]
     * @param  [type]  $deliveryId [description]
     * @param  integer $pageNo     [description]
     * @param  integer $pageSize   [description]
     * @param  [type]  $start_time [description]
     * @param  [type]  $end_time   [description]
     * @return [type]              [description]
     */
    public static function preDistributeSaleForDelivery($deliveryId,$isOuth,$pageNo=1,$pageSize=20,$start_time=null,$end_time=null){
        $condition = ['delivery_id'=>$deliveryId,'order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_SELF];
        if($isOuth == Delivery::AUTH_STATUS_AUTH){
            $condition['order_owner'] = GoodsConstantEnum::OWNER_DELIVERY;
        }
        
        $query = Order::find()->select('customer_id,SUM(customer_id)ordercount,SUM(pay_amount)total_amount')->where($condition)
            ->with(['customer.user'])
            ->offset($pageSize*($pageNo-1))->limit($pageSize)
            ->orderBy('created_at desc')
            ->groupBy('customer_id');
        if($start_time && $end_time){ 
            $query->andWhere(['and',['>=','created_at',$start_time],['<=','created_at',$end_time]]);
        }else{
            $query->andWhere("TO_DAYS(created_at)=TO_DAYS('$end_time')");
        }
        $orders = $query->asArray()->all();
        return $orders;
    }

}