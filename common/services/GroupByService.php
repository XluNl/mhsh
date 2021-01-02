<?php

namespace common\services;

use common\models\GroupActive;
use common\models\GroupRoom;
use common\models\GroupRoomOrder;
use common\models\Order;

class GroupByService
{


	/**
	 * [getGoodsIdByGroupId 通过group_id 获取团]
	 * @param  [type] $group_id [description]
	 * @return [type]           [description]
	 */
	public static function getGroupRoomByGroupId($group_id){
		return GroupRoom::find()->where(['id'=>$group_id])->one();
	}


	public static function getGroupRoomByOrderNo($order_no){
		$groupRoomOrder = GroupRoomOrder::find()->where(['order_no'=>$order_no])->one();
		return self::getGroupRoomByGroupId($groupRoomOrder['group_id']);
	}


    public static function getGroupTeamInfo($group_id){
		$groupRoom = self::getGroupRoomByGroupId($group_id);
		return CustomerService::getModelWithUser($groupRoom['team_id']);
	}

    /**
     * [getGoodInfoByActiveId 活动商品详情]
     * @param  [type] $active_id [description]
     * @return array|\yii\db\ActiveRecord|null [type]            [description]
     */
    public static function getGoodInfoByActiveId($active_id)
	{
		$info  = GroupActive::find()->where(['id'=>$active_id])->with(['schedule.goods','schedule.goodsSku','schedule.goodsDetail'])->asArray()->one();
		list($rules,$maxLevel) = self::decodeRules($info['rule_desc']);
		$info['rules'] = $rules;
		$info['max_level'] = $maxLevel;
		$info['lower_price'] = $rules[0]['price'];
		$info['active_orders'] = self::getGoodsByNumber($active_id);
		$info['group_room_successful'] = self::successfulGroupNumber($active_id);
		return $info;
	}

	public static function getGoodInfoByGroupId($group_id){
    	$groupRoom = self::getGroupRoomByGroupId($group_id);
    	$goodsInfo = self::getGoodInfoByActiveId($groupRoom['active_id']);
    	return $goodsInfo;
    }

    /**
     * [successfulGroupNumber 获取活动成团数]
     * @param  [type] $active_id [description]
     * @return [type]           [description]
     */
    public static function successfulGroupNumber($active_id){
    	return GroupRoom::find()->where(['active_id'=>$active_id,'status'=>GroupRoom::GROUP_STATUS_SUCCESSFUL])->count();
    }

    /**
     * [getOneGroupRoomOrdersByGroupId 获取一个团里订单]
     * @param  [type] $group_id [description]
     * @return [type]           [description]
     */
    public static function getOneGroupRoomOrdersByGroupId($group_id,$pay_status = Order::ORDER_STATUS_PREPARE){
    	$group_order = GroupRoomOrder::find()->where(['group_id'=>$group_id])->JoinWith(['order'=>function($query) use ($pay_status){
    		// 默认有效订单(支付订单)
    		$order_status = Order::$activeStatusArr;
    		// 获取未支付订单
    		if($pay_status == order::ORDER_STATUS_UN_PAY){
    			$order_status = [order::ORDER_STATUS_UN_PAY];
    		}
    		$query->select('*')->where(['pay_status'=>$pay_status,'order_status'=>$order_status]);
    	}])->asArray()->all();
    	return $group_order;
    }

    /**
	 * [getGroupRoomIdsByGoodsId 获取活动所在团ids]
	 * @param  [type] $good_id [description]
	 * @return [type]          [description]
	 */
	public static function getGroupRoomIdsByActiveId($active_id){
	  return GroupRoom::find()->select('id')->where(['active_id'=>$active_id])->column();
	}

    /**
     * [getGroupRoomOrdersByActiveId 获取活动所在所有团(下了订单的团)]
     * @param  [type] $active_id [description]
     * @return [type]           [description]
     */
    public static function getGroupRoomOrdersByActiveId($active_id){
    	$room_ids = self::getGroupRoomIdsByActiveId($active_id);
    	return self::getOneGroupRoomOrdersByGroupId($room_ids);
    }

    /**
	 * [getInGroupRoomNmber 根据团id 获取该团参团人数]
	 * @param  [type] $group_id [description]
	 * @return [type]           [description]
	 */
	public static function getInGroupRoomNmber($group_id){
    	return count(self::getOneGroupRoomOrdersByGroupId($group_id));
	}

	/**
     * [getGroupRoomUsersByOrderNo 通过group_id 获取该团内每个订单的用户信息]
     * @param  [type] $order_no [description]
     * @return [type]           [description]
     */
    public static function getGroupRoomUsersByGroupId($group_id){
    	// 获取房间内支付了订单的用户
    	$orders = self::getOneGroupRoomOrdersByGroupId($group_id);
    	$orderAmount = 0;
    	foreach ($orders as $key => &$value) {
    		$value['customerInfo'] = CustomerService::getModelWithUser($value['customer_id']);
    		$orderAmount+=$value['order']['pay_amount'];
    	}
    	return [$orders,$orderAmount] ;
    }

    /**
     * [getGoodsByNumber 获取此商品 已经拼团数量]
     * @param  [type] $active_id [description]
     * @return [type]           [description]
     */
    public static function getGoodsByNumber($active_id){
    	return count(self::getGroupRoomOrdersByActiveId($active_id));
    }

    public static function decodeRules($source){
		$rules = json_decode($source,true);
		$rule_desc = [];
		$maxLevel = 0;
		foreach ($rules as $key => $value) {
			if(empty($value)){
				continue;
			}
			$maxLevel+=1;

			$item = [];
			$item['name'] = ($key+1).'人团';
			$item['price'] = $value;
			$rule_desc[] = $item;
		}
		return [$rule_desc,$maxLevel];
	}

	/**
	 * [getActiveInfo 获取团信息和活动详情]
	 * @param  [type] $group_id [description]
	 * @return [type]           [description]
	 */
	public static function getActiveInfo($group_id){
		return GroupRoom::find()->where(['id'=>$group_id])->with(['activeInfo.schedule'])->asArray()->one();
	}

    /**
     * [calActiveLittleTime 计算拼团剩余时间]
     * @param $createdRoomTime_t
     * @param $continuedTime_t
     * @param $activeEndTime_t
     * @param bool $is_comparison
     * @return array [type]                  [description]
     */
	public static function calActiveLittleTime($createdRoomTime_t, $continuedTime_t, $activeEndTime_t){
		$createdRoomTime   = strtotime($createdRoomTime_t);
        $continuedTime     = $continuedTime_t*60*60;
        $activeEndTime     = strtotime($activeEndTime_t);

		// 默认剩余时间 = 创建团时间+活动持续时间 - 当前时间；
		$actualEndTime  = $createdRoomTime+$continuedTime;
		$littleTime = $actualEndTime-time();
    	if($actualEndTime >= $activeEndTime){
    		// 活动结束时间 - 当前时间
    		$littleTime = $activeEndTime-time();
    		$actualEndTime = $activeEndTime;
    	}

    	$res = [];
    	$res['createdRoomTime'] = $createdRoomTime_t;
    	$res['continuedTime'] = $continuedTime_t;
    	$res['activeEndTime'] = $activeEndTime_t;
    	$res['actualEndTime'] = date("Y-m-d H:i:s",$actualEndTime);
    	$res['littleTime'] = $littleTime;
    	return $res;
	}

    /**
     * [checkActiveTimeOut 检查团活动是否过期]
     * @param  [type] $createdRoomTime [团创建时间]
     * @param  [type] $continuedTime   [活动持续时间]
     * @param  [type] $activeEndTime   [活动结束时间]
     * @return [type]                  [description]
     */
    public static function checkActiveTimeOut($createdRoomTime, $continuedTime, $activeEndTime){
        $resInfo  = self::calActiveLittleTime($createdRoomTime, $continuedTime, $activeEndTime);
        $activeEndTime     = strtotime($activeEndTime);
        if($activeEndTime <= time()){
            return [false,'活动过期'];
        }
        // 开团时间 + 持续时间 > 活动结束时间  直接结束
        // if($createdRoomTime + $continuedTime >= $activeEndTime){
        //  return [false,'成团时间超过活动时间'];
        // }
        //开团时间 + 持续时间 < 当前时间  直接结束
        // if($createdRoomTime + $continuedTime <= time()){
        //  return [false,'成团时间超期'];
        // }
        $actualEndTime = strtotime($resInfo['actualEndTime']);
        if($actualEndTime <= time()){
            return [false,'成团时间过期'];
        }
        return [true,'没有过期'];
    }

}