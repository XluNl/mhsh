<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\Order;
use common\models\OrderLogs;

class OrderLogService
{
    /**
     * 增加订单日志（for客户）
     * @param $order
     * @param $action
     * @param $remark
     * @return array
     */
    public static function addLogForCustomer($order,$action,$remark='')
    {
        $log = new OrderLogs();
        $log->company_id = $order['company_id'];
        $log->order_no = $order['order_no'];
        $log->role = OrderLogs::ROLE_CUSTOMER;
        $log->name = $order['accept_name'];
        $log->user_id = $order['customer_id'];
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        if (!$log->save()){
            return [false,'订单日志保存失败'];
        }
        return [true,''];
    }

    /**
     * 增加订单日志(FOR 联盟商户)
     * @param $order
     * @param $action
     * @param $operatorId
     * @param $operatorName
     * @param string $remark
     * @return array
     */
    public static function addOrderLogForAlliance($order,$action,$operatorId,$operatorName,$remark='')
    {
        $log = new OrderLogs();
        $log->company_id = $order['company_id'];
        $log->order_no = $order['order_no'];
        $log->role = OrderLogs::ROLE_ALLIANCE;
        $log->name = $operatorName;
        $log->user_id = $operatorId;
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        if (!$log->save()){
            return [false,'订单日志保存失败'];
        }
        return [true,''];
    }

    /**
     * 增加订单日志（for系统）
     * @param $order Order
     * @param $action
     * @param string $remark
     * @return array
     */
    public static function addOrderLogForSystem($order,$action,$remark=''){
        $log = new OrderLogs();
        $log->company_id = $order['company_id'];
        $log->order_no = $order['order_no'];
        $log->role = OrderLogs::ROLE_SYSTEM;
        $log->name =OrderLogs::$role_list[OrderLogs::ROLE_SYSTEM];
        $log->user_id = 0;
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        if (!$log->save()){
            return [false,'订单日志保存失败'];
        }
        return [true,''];
    }

    /**
     * 增加订单日志（for admin）
     * @param $order
     * @param $action
     * @param $operatorId
     * @param $operatorName
     * @param string $remark
     * @return array
     */
    public static function addOrderLogForAdmin($order,$action,$operatorId,$operatorName,$remark=''){
        $log = new OrderLogs();
        $log->company_id = $order['company_id'];
        $log->order_no = $order['order_no'];
        $log->role = OrderLogs::ROLE_ADMIN;
        $log->name = $operatorName;
        $log->user_id = $operatorId;
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        if (!$log->save()){
            return [false,'订单日志保存失败'];
        }
        return [true,''];
    }


    /**
     * 通用日志
     * @param $role
     * @param $orderNo
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $action
     * @param $remark
     * @return array
     */
    public static function addLog($role,$orderNo,$company_id,$operatorId,$operatorName,$action,$remark)
    {
        $log = new OrderLogs();
        $log->company_id =$company_id;
        $log->order_no = $orderNo;
        $log->role = $role;
        $log->name = $operatorName;
        $log->user_id = $operatorId;
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        if (!$log->save()){
            return [false,'订单日志保存失败'];
        }
        return [true,''];
    }

}