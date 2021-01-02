<?php


namespace console\services;


use common\models\GroupRoom;
use common\models\GroupRoomWaitRefundOrder;
use common\models\OrderLogs;
use Yii;

class GroupOrderService extends \common\services\GroupOrderService
{

    /**
     * 批量关团
     * @param $nowTime
     * @return array[]
     */
    public static function  batchCloseGroupRoom($nowTime){
        $readyCloseGroupRoomQuery = GroupRoom::find()->where([
            'and',
            ['status'=>GroupRoom::GROUP_STATUS_PROCESSING],
            "place_count<=paid_order_count",
            [
                'or',
                [
                    'and',
                    ['<=','expect_finished_at',$nowTime]
                ],
                [
                    'and',
                    "paid_order_count>=max_level"
                ],
            ]
        ]);
        $successList = [];
        $failedList = [];
        foreach ($readyCloseGroupRoomQuery->each(20) as $room){
            list($success,$errorMsg) = self::autoCloseGroupRoom($room);
            if ($success){
                $successList[] = $room['room_no'];
            }
            else{
                $failedList[] = [
                    'room_no'=>$room['room_no'],
                    "error"=>$errorMsg
                ];
            }
        }
        return [$successList,$failedList];
    }

    /**
     * 关团
     * @param $room
     * @return array
     */
    private static function autoCloseGroupRoom($room){
        $paymentSdk = Yii::$app->frontendWechat->payment;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            list($success,$errorMsg) = self::closeRoomCommon($room['room_no'],$room['company_id'],$paymentSdk,OrderLogs::ROLE_SYSTEM,0,OrderLogs::$role_list[OrderLogs::ROLE_SYSTEM],"成团结算",false);
            $transaction->commit();
            return [$success,$errorMsg];
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            return [false,$e->getMessage()];
        }
    }

    /**
     * 批量执行退款单
     * @param $nowTime
     * @return array[]
     */
    public static function batchDoGroupRoomRefund($nowTime){
        $waitRefundOrderModels = GroupRoomWaitRefundOrder::find()->where([
            'and',
            ['status'=>GroupRoomWaitRefundOrder::REFUND_STATUS_WAIT],
        ])->asArray();
        $successList = [];
        $failedList = [];
        foreach ($waitRefundOrderModels->each(20) as $waitRefundOrder){
            list($success,$errorMsg) = self::autoDoGroupRoomRefund($waitRefundOrder['id']);
            if ($success){
                $successList[] = $waitRefundOrder['id'];
            }
            else{
                $failedList[] = [
                    'id'=>$waitRefundOrder['id'],
                    "error"=>$errorMsg
                ];
            }
        }
        return [$successList,$failedList];
    }

    /**
     * 执行退款单
     * @param $groupRoomWaitRefundOrderId
     * @return array
     */
    private static function autoDoGroupRoomRefund($groupRoomWaitRefundOrderId){
        $paymentSdk = Yii::$app->frontendWechat->payment;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            list($success,$errorMsg) = self::doRealRefundOneOrder($groupRoomWaitRefundOrderId,$paymentSdk,OrderLogs::ROLE_SYSTEM,0,OrderLogs::$role_list[OrderLogs::ROLE_SYSTEM],"执行拼团退款单");
            $transaction->commit();
            return [$success,$errorMsg];
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            return [false,$e->getMessage()];
        }
    }
}
