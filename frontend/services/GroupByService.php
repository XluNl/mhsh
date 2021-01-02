<?php


namespace frontend\services;


use common\models\CommonStatus;
use common\models\GoodsSort;
use common\utils\StringUtils;
use yii\db\Query;

use common\models\Goods;
use common\models\GoodsSku;
use common\models\GoodsSchedule;
use common\models\GoodsDetail;
use common\models\Order;
use common\models\OrderGoods;
use common\models\GroupActive;
use common\models\GroupActiveRules;
use common\models\GroupRoom;
use common\models\GroupRoomOrder;
use common\models\Customer;
use common\models\Common;
use common\models\GoodsConstantEnum;

class GroupByService extends \common\services\GroupByService
{

    /**
     * [getGoodList 获取团购商品列表
     * @param $companyId
     * @param $pageNo
     * @param $pageSize
     * @return array|\yii\db\ActiveRecord[] [type] [description]
     */
    public static function getGoodsList($companyId, $pageNo=1, $pageSize=20)
    {

        $query = GroupActive::find()->select("a.id,a.schedule_id,a.rule_desc")
            ->from(GroupActive::tableName() . ' as a')
            ->where(["a.company_id" => $companyId])
            ->with(['schedule.goods', 'schedule.goodsSku'])
            ->joinWith(['schedule' => function ($query) {
                $query->where(["schedule_status" => GoodsConstantEnum::STATUS_UP]);
                $query->andWhere(['schedule_display_channel' => GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_GROUP]);
                $query->andWhere('online_time <= NOW()');
                $query->andWhere('offline_time >= NOW()');
            }, 'schedule.goods' => function ($query) {
                $query->where(['goods_status' => GoodsConstantEnum::STATUS_UP]);
            }, 'schedule.goodsSku' => function ($query) {
                $query->where(['sku_status' => GoodsConstantEnum::STATUS_UP]);
            }]);
        return $query->offset(($pageNo - 1) * $pageSize)->limit($pageSize)->asArray()->all();
    }

    /**
     * [getGoodsDetail 获取商品详情]
     * @param $companyId
     * @param $activeId
     * @return array|\yii\db\ActiveRecord|null [type] [description]
     */
    public static function getGoodsDetail($companyId, $activeId)
    {
        $goodsInfo = self::getGoodInfoByActiveId($activeId);
        $goodsInfo['group_room_list'] = self::getGoodsGroupRoomList($companyId, $activeId);
        foreach ($goodsInfo['group_room_list'] as $key => &$value) {
            unset($value['activeInfo']);
        }
        $goodsInfo['users'] = self::getActiveLatelyPlayedUser($activeId);
        return $goodsInfo;
    }

    /**
     * [getGroupList 获取商品 团列表及每个团参团人数]
     * @return [type] [description]
     */
    public static function getGoodsGroupList($company_id)
    {
        $sql = " SELECT a.*,b.pay_status,c.* FROM `sptx_group_room_order` AS a JOIN `sptx_order` AS b ON a.order_no = b.order_no JOIN `sptx_group_room` AS c ON c.id=a.group_id WHERE b.pay_status=1";
        return GroupRoomOrder::findBySql($sql)->asArray()->all();
    }

    /**
     * [getActiveLatelyByUser 获取最近参与活动的3个用户]
     * @param $active_id
     * @param int $number
     * @return array [type] [description]
     */
    public static function getActiveLatelyPlayedUser($active_id, $number = 3)
    {
        $room_ids = self::getGroupRoomIdsByActiveId($active_id);
        $orders = GroupRoomOrder::find()->where(['group_id' => $room_ids])->orderBy('created_at desc')->offset(0)->limit($number)->all();
        $users = [];
        foreach ($orders as $key => $value) {
            $user = CustomerService::getModelWithUser($value['customer_id']);
            $users[] = Common::generateAbsoluteUrl($user['user']['head_img_url']);
        }
        return $users;
    }

    /**
     * [getGoodsGroupRoomList 获取该商品所有的团列表]
     * @param  [type] $company_id [description]
     * @param  [type] $active_id   [description]
     * @return [type]             [description]
     */
    public static function getGoodsGroupRoomList($company_id, $active_id)
    {
        $groupRoom = GroupRoomOrder::find()->from(GroupRoomOrder::tableName() . ' as a')
            ->select('count(*)order_number,a.*')
            ->where(['a.company_id' => $company_id])
            ->JoinWith(['order' => function ($query) {
                $query->select('count(*)number,id,pay_status,order_no,customer_id')->where(['pay_status' => 1]);
            }])
            ->JoinWith(['groupRoom' => function ($query) use ($active_id) {
                $query->where(['active_id' => $active_id]);
                $query->andWhere(['status' => GroupRoom::GROUP_STATUS_PROCESSING]);
            }])->groupBy('group_id')->asArray()->all();

        foreach ($groupRoom as $key => &$value) {
            // $value['teamInfo']   = self::getGroupTeamInfo($value['group_id']);
            $groupRoomInfo = self::getActiveInfo($value['group_id']);
            $value['activeInfo'] = $groupRoomInfo['activeInfo'];
            /*
                计算活动倒计时
                1,获取团创建时间(这个团必须订单支付成功)
                2,获取活动持续
             */
            $createdRoomTime = $groupRoomInfo['created_at'];
            $continuedTime = $value['activeInfo']['continued'];
            $activeEndTIme = $value['activeInfo']['schedule']['offline_time'];
            $value['littleTime'] = self::calActiveLittleTime($createdRoomTime, $continuedTime, $activeEndTIme);
            unset($value['activeInfo']);
            unset($value['order']);

            if ($value['groupRoom']['paid_order_count'] >= $value['groupRoom']['max_level']) {
                $value['groupRoom']['status'] = GroupRoom::GROUP_STATUS_SUCCESSFUL;
                $value['groupRoom']['status_text'] = "人数已满";
            } elseif ($value['groupRoom']['place_count'] >= $value['groupRoom']['max_level']) {
                $value['groupRoom']['status'] = GroupRoom::GROUP_STATUS_SUCCESSFUL;
                $value['groupRoom']['status_text'] = "还有机会";
            } else {
                $value['groupRoom']['status_text'] = "我要参团";
            }
        }
        return $groupRoom;
    }





    /**
     * [getActiveInfoByActiveId active_id获取活动信息]
     * @param  [type] $active_id [description]
     * @return [type]            [description]
     */
    public static function getActiveInfoByActiveId($active_id)
    {
        return GroupActive::find()->select('id,schedule_id,continued,rule_desc,status')->with(['schedule'])->where(['id' => $active_id])->asArray()->one();
    }

    /**
     * [getByGoodsNumber 获取用户下单的具体商品]
     * @param  [type] $customer_id [description]
     * @param  [type] $order_no    [description]
     * @param  [type] $goods_id    [description]
     * @return [type]              [description]
     */
    public static function getOrderGoods($order_no, $goods_id)
    {
        return OrderGoods::find()->where(['order_no' => $order_no, 'goods_id' => $goods_id])->asArray()->one();
    }

    /**
     * [getUserOrders 通过用户id 获取用户的订单]
     * @param  [type] $customer_id [description]
     * @return [type] $pay_status  [订单状态]
     */
    public static function getUserOrdersMake($company_id, $customer_id, $pay_status = Order::ORDER_STATUS_PREPARE)
    {
        $roomOrderQuery = GroupRoomOrder::find()->from(GroupRoomOrder::tableName() . ' as a')->where(['a.customer_id' => $customer_id])
            ->innerJoinWith(['order' => function ($query) use ($pay_status) {
                $query->select('id,order_no,pay_status,goods_num')->where(['pay_status' => $pay_status]);
            }]);
        return $roomOrderQuery;
    }

    /**
     * [getUserGroupRooms 获取用户参加的所有待成团的团
     * @param  [type] $company_id  [description]
     * @param  [type] $customer_id [description]
     * @return [type]              [description]
     */
    public static function getUserGroupRooms($company_id, $customer_id, $status = GroupRoom::GROUP_STATUS_PROCESSING)
    {
        $groupRooms = GroupRoom::find()->where(['company_id' => $company_id, 'status' => $status])->joinWith(['groupOrders' => function ($query) use ($customer_id) {
            $query->where(['customer_id' => $customer_id]);
        }])->asArray()->all();
        return $groupRooms;
    }

    /**
     * [getOrderList description]
     * @param  [type] $company_id  [description]
     * @param  [type] $customer_id [description]
     * @param  [type] $type        [all 全部;waitSuccess 待成团;waitFh 待发货;waitQh 待取货;success 已完成]
     * @param  [type] $kew_word    [商品名字搜索]
     * @return [type]              [description]
     */
    public static function getOrderList($company_id, $customer_id, $page_no = 1, $page_size = 20, $type, $key_word)
    {
        $pay_status = Order::ORDER_STATUS_PREPARE;
        $status = GroupRoom::GROUP_STATUS_PROCESSING;
        switch ($type) {
            case 'waitSuccess':
                $status = GroupRoom::GROUP_STATUS_PROCESSING;
                break;
            case 'waitFh':
                $status = GroupRoom::GROUP_STATUS_SUCCESSFUL;
                break;
            case 'waitQh':
                $pay_status = Order::ORDER_STATUS_SELF_DELIVERY;
                $status = GroupRoom::GROUP_STATUS_SUCCESSFUL;
                break;
            case 'success':
                $pay_status = Order::ORDER_STATUS_COMPLETE;
                $status = GroupRoom::GROUP_STATUS_SUCCESSFUL;
                break;
            case 'waitPayment':
                $pay_status = Order::ORDER_STATUS_UN_PAY;
                $status = GroupRoom::GROUP_STATUS_PROCESSING;
                break;
            default:
                // 全部
                $pay_status = array_merge([Order::ORDER_STATUS_UN_PAY], Order::$activeStatusArr);
                $status = [GroupRoom::GROUP_STATUS_PROCESSING, GroupRoom::GROUP_STATUS_SUCCESSFUL];
                break;
        }
        // 获取用户参与的支付了订单的团
        $groupRoomOrders = self::getUserOrdersMake($company_id, $customer_id, $pay_status)
            ->joinWith(['groupRoom' => function ($query) use ($status) {
                $query->where(['status' => $status]);
            }]);

        if (!empty($key_word)) {
            $groupRoomOrders->joinWith(['groupRoom.activeInfo.schedule.goods' => function ($query) use ($key_word) {
                $query->where(['like', 'goods_name', $key_word]);
            }]);
        }

        $groupRoomOrders = $groupRoomOrders->offset(($page_no - 1) * $page_size)->limit($page_size)->asArray()->all();
        foreach ($groupRoomOrders as $key => &$value) {
            list($orders, $orderAmount) = self::getGroupRoomUsersByGroupId($value['group_id']);
            $groupRoom = self::getGroupRoomByGroupId($value['group_id']);
            $activeInfo = self::getGoodInfoByGroupId($value['group_id']);
            $value['goodsInfo'] = self::activeInfoDisplayField($activeInfo);
            $value['users'] = self::userSort(self::getUsersByOrders($orders), $groupRoom['team_id']);
            $value['orderCount'] = count($orders);
            $value['orderAmount'] = Common::showAmount($orderAmount);
            $value['littleTime'] = self::calActiveLittleTime($groupRoom['created_at'], $activeInfo['continued'], $activeInfo['schedule']['offline_time']);
        }

        return $groupRoomOrders;
    }

    /**
     * [waitSuccessGroupRoom 根据用户id 获取用户待成团订单]
     * @param  [type] $customer_id [description]
     * @return [type]              [description]
     */
    public static function waitSuccessGroupRoom($company_id, $customer_id)
    {
        // 获取用户参与的支付了订单的团
        $groupRoomOrders = self::getUserOrdersMake($company_id, $customer_id)->joinWith(['groupRoom' => function ($query) {
            // 过滤拼团还未成功的团
            $query->where(['status' => GroupRoom::GROUP_STATUS_PROCESSING]);
        }])->asArray()->all();;

        // 获取每个订单所在团 用户信息
        foreach ($groupRoomOrders as $key => &$value) {
            list($orders, $orderAmount) = self::getGroupRoomUsersByGroupId($value['group_id']);
            $groupRoom = self::getGroupRoomByGroupId($value['group_id']);
            $value['users'] = $orders;
            $value['orderCount'] = count($orders);
            $value['orderAmount'] = $orderAmount;
            $value['activeInfo'] = self::getGoodInfoByGroupId($value['group_id']);
            $value['littleTime'] = self::calActiveLittleTime($groupRoom['created_at'], $value['activeInfo']['continued'], $value['activeInfo']['schedule']['offline_time']);
            unset($value['activeInfo']['schedule']['goodsDetail']);
            unset($value['activeInfo']['active_orders']);
            unset($value['activeInfo']['group_room_successful']);
        }
        return $groupRoomOrders;
    }

    public static function activeInfoDisplayField($activeInfo)
    {
        if (empty($activeInfo)) {
            return [];
        }
        $res = [];
        if (isset($activeInfo['schedule']['goods'])) {
            $res['id'] = $activeInfo['schedule']['goods']['id'];
            $res['goods_name'] = $activeInfo['schedule']['goods']['goods_name'];
            $res['goods_img'] = Common::generateAbsoluteUrl($activeInfo['schedule']['goods']['goods_img']);
        }
        if (isset($activeInfo['schedule']['goodsSku'])) {
            $res['sku_name'] = $activeInfo['schedule']['goodsSku']['sku_name'];
            $res['sku_img'] = Common::generateAbsoluteUrl($activeInfo['schedule']['goodsSku']['sku_img']);
            $res['sku_unit'] = $activeInfo['schedule']['goodsSku']['sku_unit'];
            $res['sku_unit_factor'] = $activeInfo['schedule']['goodsSku']['sku_unit_factor'];
            $res['sku_stock'] = $activeInfo['schedule']['goodsSku']['sku_stock'];
            $res['sku_sold'] = $activeInfo['schedule']['goodsSku']['sku_sold'];
            $res['sale_price'] = Common::showAmount($activeInfo['schedule']['goodsSku']['sale_price']);
            $res['purchase_price'] = Common::showAmount($activeInfo['schedule']['goodsSku']['purchase_price']);
            $res['reference_price'] = Common::showAmount($activeInfo['schedule']['goodsSku']['reference_price']);
        }

        return $res;
    }

    /**
     * [getOrderDetail 订单详情]
     * @param  [type]  $order_no      [description]
     * @param boolean $status [description]
     * @return [type]                 [description]
     */
    public static function orderDetail($customer_id, $order_no, $order_type = null)
    {
        $groupRoomOrder = GroupRoomOrder::find()->where(['order_no' => $order_no, 'customer_id' => $customer_id])->one();
        // 订单用户信息
        $customerInfo = Customer::find()->select('id,user_id,invite_code,phone')->where(['id' => $groupRoomOrder['customer_id']])->with(['user'])->asArray()->one();
        // 获取此订单所在团的其他订单用户信息
        list($orders, $totalAmount) = self::getGroupRoomUsersByGroupId($groupRoomOrder['group_id']);
        // 订单信息
        $order = Order::find()->where(['order_no' => $order_no])->with(['goods'])->one();
        $groupRoom = self::getGroupRoomByGroupId($groupRoomOrder['group_id']);
        $activeInfo = self::getGoodInfoByGroupId($groupRoom['id']);
        unset($activeInfo['schedule']['goodsDetail']);

        $res = [];
        // AddressService::getAddressById($addressId,$cModel['id']);
        $res['groupRoom'] = $groupRoom;
        $res['activeInfo'] = $activeInfo;
        $res['goods_info'] = self::activeInfoDisplayField($activeInfo);
        $res['orderInfo'] = $order;
        $res['groupRoomOrder'] = $groupRoomOrder;
        $res['users'] = self::userSort(self::getUsersByOrders($orders), $groupRoom['team_id']);
        $res['totalAmount'] = Common::showAmount($totalAmount);
        // $res['teamInfo']    = self::getUserinfo($groupRoom['team_id']);

        switch (trim($order_type)) {
            case 'waitSuccess':
                // 未成团 计算活动剩余时间
                $res['littleTime'] = self::calActiveLittleTime($groupRoom['created_at'], $res['activeInfo']['continued'], $res['activeInfo']['schedule']['offline_time']);
                break;
            case 'success':
                // 计算成团后团购买信息
                $maxLevel = $res['activeInfo']['max_level'];
                $max = $res['activeInfo']['rules'][$maxLevel - 1]['price'];
                $mix = $res['activeInfo']['rules'][0]['price'];
                $byNum = count($res['users']);
                $groupByInfo = [
                    'maxLevel' => $maxLevel,
                    'max' => $max,
                    'mix' => $mix,
                    'byNum' => $byNum,
                    'diffAmount' => $max - $mix,
                    'totalDiff' => ($max - $mix) * $byNum
                ];
                $res['groupByInfo'] = $groupByInfo;
                break;
            default:
                # code...
                break;
        }
        unset($res['activeInfo']);
        return $res;
    }

    public static function userSort($users, $team_id)
    {
        $items = [];
        foreach ($users as $key => $value) {
            if ($value['user_id'] == $team_id) {
                $items[0] = $value;
            } else {
                $items[$key + 1] = $value;
            }
        }
        sort($items);
        return array_values($items);
    }

    public static function getUsersByOrders($orders)
    {
        if (empty($orders)) {
            return [];
        }
        $users = [];
        foreach ($orders as $key => $value) {
            $item = [];
            $item['group_id'] = $value['group_id'];
            $item['group_no'] = $value['group_no'];
            $item['order_no'] = $value['order']['order_no'];
            $item['goods_num'] = $value['order']['goods_num'];
            $item['three_pay_amount'] = $value['order']['three_pay_amount'];
            $item['pay_status'] = $value['order']['pay_status'];
            $item['pay_time'] = $value['order']['pay_time'];
            $item['created_at'] = $value['order']['created_at'];
            $item['pay_name'] = $value['order']['pay_name'];
            $item['user_id'] = $value['customerInfo']['id'];
            $item['phone'] = $value['customerInfo']['phone'];
            $item['nickname'] = $value['customerInfo']['user']['nickname'];
            $item['head_img_url'] = Common::generateAbsoluteUrl($value['customerInfo']['user']['head_img_url']);
            $users[] = $item;
        }
        return $users;
    }

    /**
     * [waitDeliverOrder 获取用户拼团成功的订单(已支付->待发货)]
     * @param  [type] $company_id  [description]
     * @param  [type] $customer_id [description]
     * @return [type]              [description]
     */
    public static function successOrderList($company_id, $customer_id)
    {
        // 获取用户所有团订单
        $userOrders = self::getUserOrdersMake($company_id, $customer_id)->joinWith(['groupRoom' => function ($query) {
            // 选择拼团成功的团
            $query->where(['status' => GroupRoom::GROUP_STATUS_SUCCESSFUL]);
        }])->asArray()->all();
        return $userOrders;
    }

    /**
     * [WaitPaymentOrders 获取用户 待付款的团订单(代付款)]
     * @param [type] $company_id  [description]
     * @param [type] $customer_id [description]
     */
    public static function WaitPaymentOrdersList($company_id, $customer_id)
    {
        $userOrders = self::getUserOrdersMake($company_id, $customer_id, Order:: ORDER_STATUS_UN_PAY)->joinWith(['groupRoom' => function ($query) {
            // 选择拼团还未成功的团
            $query->where(['status' => GroupRoom::GROUP_STATUS_PROCESSING]);
        }])->asArray()->all();
        return $userOrders;
    }

    /**
     * [shareInGroupRoom 分享链接进入的用户]
     * @param  [type] $company_id [公司id]
     * @param  [type] $active_id  [活动id]
     * @param  [type] $group_id   [团id]
     * @return [type]             [description]
     */
    public static function shareInGroupRoom($active_id, $group_id)
    {
        $groupRoom = self::getActiveInfo($group_id);
        list($orders, $orderAmount) = self::getGroupRoomUsersByGroupId($group_id);
        $users = [];
        foreach ($orders as $key => $order) {
            $item = [];
            $item[] = $order['customerInfo']['user']['head_img_url'];
            $users[] = $item;
        }
        $teamInfo = CustomerService::getModelWithUser($groupRoom['team_id']);;
        $activeInfo = $groupRoom['activeInfo'];
        $littleTime = self::calActiveLittleTime($groupRoom['created_at'], $activeInfo['continued'], $activeInfo['schedule']['offline_time']);

        $res = [
            'users' => $users,
            'teamInfo' => $teamInfo,
            'littleTime' => $littleTime
        ];
        return $res;
    }

}