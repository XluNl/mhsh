<?php


namespace common\services;


use backend\services\CustomerService;
use common\models\CommonStatus;
use common\models\Customer;
use common\models\CustomerInvitation;
use common\models\CustomerInvitationLevel;
use common\models\Order;
use common\models\OrderPreDistribute;
use common\models\UserInfo;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CustomerInvitationService
{
    /**
     * 找到父节点依赖
     * @param $id
     * @return array|bool|null
     */
    public static function getInvitationById($id){
        if (StringUtils::isBlank($id)){
            return null;
        }
        $conditions = ['customer_id' => $id, 'status' =>CommonStatus::STATUS_ACTIVE];
        $result = (new Query())->from(CustomerInvitation::tableName())->where($conditions)->one();
        return $result===false?null:$result['parent_id'];
    }

    /**
     * 查找子节点
     * @param $ids
     * @return array
     */
    public static function getInvitationByParentId($ids){
        $conditions = ['parent_id' => $ids, 'status' =>CommonStatus::STATUS_ACTIVE];
        $result = (new Query())->from(CustomerInvitation::tableName())->where($conditions)->all();
        return $result==null?[]:$result;
    }

    /**
     * 查询子节点（带用户信息&邀请统计）
     * @param $ids
     * @return array
     */
    public static function getCustomerByParentId($ids){
        $conditions = ['parent_id' => $ids, CustomerInvitation::tableName().".status" =>CommonStatus::STATUS_ACTIVE];
        $result = (new Query())->from(CustomerInvitation::tableName())
            ->leftJoin(Customer::tableName(),Customer::tableName().".id=".CustomerInvitation::tableName().'.customer_id')
            ->leftJoin(CustomerInvitationLevel::tableName(),CustomerInvitationLevel::tableName().".customer_id=".CustomerInvitation::tableName().'.customer_id')
            ->innerJoin(UserInfo::tableName(),Customer::tableName().".user_id=".UserInfo::tableName().'.id')
            ->where($conditions)->all();
        CustomerInvitationDomainService::batchSetInvitationLevelText($result);
        CustomerInvitationDomainService::batchSetPhoneMark($result);
        $result= GoodsDisplayDomainService::batchRenameImageUrl($result,'head_img_url');
        return $result==null?[]:$result;
    }



    /**
     * 一级分润详情
     * @param $id
     * @param $startTime
     * @param $endTime
     * @param $oneLevelIds
     * @return array
     */
    public static function getOneLevelInvitationDetail($id, $startTime=null, $endTime=null,$oneLevelIds = null){
        $orderPreDistributeTable = OrderPreDistribute::tableName();
        $orderTable = Order::tableName();
        $conditions = [
            'and',
            [
                "{$orderPreDistributeTable}.biz_id"=>$id,
                "{$orderPreDistributeTable}.level"=>OrderPreDistribute::LEVEL_ONE,
                "{$orderPreDistributeTable}.biz_type"=>OrderPreDistribute::BIZ_TYPE_CUSTOMER,
                "{$orderTable}.order_status"=>[Order::ORDER_STATUS_PREPARE,Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_RECEIVE,Order::ORDER_STATUS_COMPLETE],
            ]
        ];
        if (!StringUtils::isBlank($startTime)){
            $conditions[] = ['>=', "{$orderPreDistributeTable}.order_time", $startTime];
        }
        if (!StringUtils::isBlank($endTime)){
            $conditions[] = ['<=',  "{$orderPreDistributeTable}.order_time", $endTime];
        }
        if (!empty($oneLevelIds)){
            $conditions[] = ["{$orderTable}.customer_id"=>$oneLevelIds];
        }
        $detail = (new Query())->from($orderPreDistributeTable)
            ->select([
                "COALESCE(SUM({$orderPreDistributeTable}.amount),0) as amount",
                "COALESCE(SUM({$orderPreDistributeTable}.amount_ac),0) as amount_ac",
                "COALESCE(SUM({$orderPreDistributeTable}.order_amount),0) as order_amount",
                "COUNT(DISTINCT({$orderTable}.order_no)) as order_count",
                "{$orderTable}.one_level_rate_id as one_level_customer_id",
                "{$orderTable}.customer_id as customer_id"
            ])
            ->innerJoin($orderTable,"{$orderPreDistributeTable}.order_no={$orderTable}.order_no")
            ->where($conditions)
            ->groupBy("{$orderTable}.customer_id")->all();
        return $detail;
    }


    /**
     * 二级分润详情
     * @param $id
     * @param null $startTime
     * @param null $endTime
     * @param null $oneLevelIds
     * @return array
     */
    public static function getTwoLevelInvitationDetail($id,$startTime=null,$endTime=null,$oneLevelIds = null){
        $orderPreDistributeTable = OrderPreDistribute::tableName();
        $orderTable = Order::tableName();
        $conditions = [
            'and',
            [
                "{$orderPreDistributeTable}.biz_id"=>$id,
                "{$orderPreDistributeTable}.biz_type"=>OrderPreDistribute::BIZ_TYPE_CUSTOMER,
                "{$orderPreDistributeTable}.level"=>OrderPreDistribute::LEVEL_TWO,
                "{$orderTable}.order_status"=>[Order::ORDER_STATUS_PREPARE,Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_RECEIVE,Order::ORDER_STATUS_COMPLETE],
            ]
        ];
        if (!StringUtils::isBlank($startTime)){
            $conditions[] = ['>=', "{$orderPreDistributeTable}.order_time", $startTime];
        }
        if (!StringUtils::isBlank($endTime)){
            $conditions[] = ['<=',  "{$orderPreDistributeTable}.order_time", $endTime];
        }
        if (!empty($oneLevelIds)){
            $conditions[] = ["{$orderTable}.one_level_rate_id"=>$oneLevelIds];
        }
        $detail = (new Query())->from($orderPreDistributeTable)
            ->select([
                "COALESCE(SUM({$orderPreDistributeTable}.amount),0) as amount",
                "COALESCE(SUM({$orderPreDistributeTable}.amount_ac),0) as amount_ac",
                "COALESCE(SUM({$orderPreDistributeTable}.order_amount),0) as order_amount",
                "COUNT(DISTINCT({$orderTable}.order_no)) as order_count",
                "{$orderTable}.one_level_rate_id as one_level_customer_id",
                "{$orderTable}.two_level_rate_id as two_level_customer_id",
                "{$orderTable}.customer_id as customer_id"
            ])
            ->innerJoin($orderTable,"{$orderPreDistributeTable}.order_no={$orderTable}.order_no")
            ->where($conditions)->groupBy("{$orderTable}.one_level_rate_id,{$orderTable}.customer_id")->all();
        return $detail;
    }



    /**
     * @param array $oneLevel
     * @param array $oneLevelDetail
     * @param array $twoLevel
     * @param array $twoLevelDetail
     * @param array $detail
     * @return array
     */
    public static function assembleCustomerInvitationData(array $oneLevel, array $oneLevelDetail, array $twoLevel, array $twoLevelDetail, array $detail)
    {
        foreach ($oneLevel as $one) {
            $o = [
                'phone_org' => $one['phone'],
                'phone' => $one['phone_text'],
                'name' => $one['nickname'],
                'head_img_url' => $one['head_img_url'],
                'level' => $one['level'],
                'level_text' => $one['level_text'],
                'customer_id' => $one['customer_id'],
                'amount' => 0,
                'amount_ac' => 0,
                'order_count' => 0,
                'order_amount' => 0,
                'children' => []
            ];
            foreach ($oneLevelDetail as $oneDetail) {
                if ($oneDetail['customer_id'] == $one['customer_id']) {
                    $o['amount'] += $oneDetail['amount'];
                    $o['amount_ac'] += $oneDetail['amount_ac'];
                    $o['order_amount'] += $oneDetail['order_amount'];
                    $o['order_count'] += $oneDetail['order_count'];
                }
            }

            foreach ($twoLevel as $two) {
                if ($two['parent_id'] == $one['customer_id']) {
                    $t = [
                        'phone_org' => $two['phone'],
                        'phone' => $two['phone_text'],
                        'name' => $two['nickname'],
                        'head_img_url' => $two['head_img_url'],
                        'level' => $two['level'],
                        'level_text' => $two['level_text'],
                        'customer_id' => $two['customer_id'],
                        'amount' => 0,
                        'amount_ac' => 0,
                        'order_count' => 0,
                        'order_amount' => 0,
                    ];
                    foreach ($twoLevelDetail as $twoDetail) {
                        if ($twoDetail['customer_id'] == $two['customer_id']) {
                            $t['amount'] += $twoDetail['amount'];
                            $t['amount_ac'] += $twoDetail['amount_ac'];
                            $t['order_amount'] += $twoDetail['order_amount'];
                            $t['order_count'] += $twoDetail['order_count'];
                        }
                    }
                    $o['children'][] = $t;
                }
            }
            $detail[] = $o;
        }
        return $detail;
    }

    /**
     * 一级子节点
     * @param $startTime
     * @param $endTime
     * @param null $customerId
     * @return array
     */
    public static function statisticOneTwoLevel($startTime, $endTime, $customerId=null){

        $customerInvitationTable =CustomerInvitation::tableName();
        $customerTable = Customer::tableName();

    /*    $conditions = [
            'and',
            ["{$customerInvitationTable}.status" =>CommonStatus::STATUS_ACTIVE],
            ['>=',"{$customerInvitationTable}.created_at",$startTime],
            ['<=',"{$customerInvitationTable}.created_at",$endTime],
        ];
        if (StringUtils::isNotBlank($customerId)){
            $conditions[] = ['parent_id'=>$customerId];
        }

        $customerInvitations = (new Query())->from(CustomerInvitation::tableName())
            ->select([
                "{$customerTable}.*",
                "{$customerInvitationTable}.created_at as invite_time",
                "parent_id",
                "customer_id",
            ])
            ->where($conditions)->leftJoin($customerTable,"{$customerTable}.id={$customerInvitationTable}.customer_id")
            ->all();
        $customerInvitationResult = [];
        foreach ($customerInvitations as $v){
            if (!key_exists($v['parent_id'],$customerInvitationResult)){
                $customerInvitationResult[$v['parent_id']] = [
                    'customer_id'=>$v['parent_id'],
                    'invitation_count'=>0,
                    'children'=>[]
                ];
            }
            $customerInvitationResult[$v['parent_id']]['invitation_count']++;
            $customerInvitationResult[$v['parent_id']]['children'][]=[
                'child_customer_id'=> $v['customer_id'],
                'child_customer_name'=> $v['nickname'],
                'child_customer_phone'=> $v['phone'],
                'child_customer_invite_time'=>$v['invite_time'],
            ];
        }*/


        $conditions = [
            'and',
            [
                "t1.status" =>CommonStatus::STATUS_ACTIVE,
            ],
            ['>=',"t1.created_at",$startTime],
            ['<=',"t1.created_at",$endTime],
        ];
        if (StringUtils::isNotBlank($customerId)){
            $conditions[] = ['t1.parent_id'=>$customerId];
        }

        $customerInvitations = (new Query())
            ->from(['t1'=>$customerInvitationTable])
            ->leftJoin(['t2' => $customerInvitationTable], '[[t2.parent_id]]=[[t1.customer_id]]')
            ->leftJoin(['c1' => $customerTable], '[[t1.parent_id]]=[[c1.id]]')
            ->leftJoin(['c2' => $customerTable], '[[t1.customer_id]]=[[c2.id]]')
            ->leftJoin(['c3' => $customerTable], '[[t2.customer_id]]=[[c3.id]]')
            ->select([
                "t1.parent_id customer_id1",
                "c1.nickname nickname1",
                "c1.phone phone1",
                "t1.customer_id customer_id2",
                "t1.created_at invite_time2",
                "c2.nickname nickname2",
                "c2.phone phone2",
                "t2.customer_id customer_id3",
                "t2.created_at invite_time3",
                "c3.nickname nickname3",
                "c3.phone phone3",
                "t2.is_connect is_connect3"
            ])
            ->where($conditions)
            ->all();

        $customerInvitationResult = [];
        foreach ($customerInvitations as $v){
            if (!key_exists($v['customer_id1'],$customerInvitationResult)){
                $customerInvitationResult[$v['customer_id1']] = [
                    'customer_id'=>$v['customer_id1'],
                    'customer_name'=>$v['nickname1'],
                    'customer_phone'=>$v['phone1'],
                    'invitation_count'=>0,
                    'invitation_children_count'=>0,
                    'children'=>[]
                ];
            }
            if (!key_exists($v['customer_id2'],$customerInvitationResult[$v['customer_id1']]['children'])){
                $customerInvitationResult[$v['customer_id1']]['children'][$v['customer_id2']]=[
                    'child_customer_id'=> $v['customer_id2'],
                    'child_customer_name'=> $v['nickname2'],
                    'child_customer_phone'=> $v['phone2'],
                    'child_customer_invite_time'=>$v['invite_time2'],
                    'child_customer_is_connect'=>CustomerInvitation::IS_CONNECT_TRUE,
                    'invitation_count'=>0,
                    'children'=>[]
                ];
                $customerInvitationResult[$v['customer_id1']]['invitation_count']++;
            }

            if (StringUtils::isNotBlank($v['customer_id3'])){
                if (DateTimeUtils::isBetweenStr($v['invite_time3'],$startTime,$endTime)){
                    $customerInvitationResult[$v['customer_id1']]['children'][$v['customer_id2']]['children'][]=[
                        'child_customer_id'=> $v['customer_id3'],
                        'child_customer_name'=> $v['nickname3'],
                        'child_customer_phone'=> $v['phone3'],
                        'child_customer_invite_time'=>$v['invite_time3'],
                        'child_customer_is_connect'=>$v['is_connect3'],
                    ];
                    if (CustomerInvitation::IS_CONNECT_TRUE==$v['is_connect3']){
                        $customerInvitationResult[$v['customer_id1']]['children'][$v['customer_id2']]['invitation_count']++;
                        $customerInvitationResult[$v['customer_id1']]['invitation_children_count']++;
                    }
                }
            }

        }

        return $customerInvitationResult;
    }

    /**
     * 获取上级信息
     * @param $customerId
     * @return array|bool|Customer|\yii\db\ActiveRecord|null
     */
    public static function getParentCustomerInfo($customerId){
        $parentId = self::getInvitationById($customerId);
        if (StringUtils::isNotBlank($parentId)){
            return CustomerService::getActiveModel($parentId);
        }
        return null;
    }

}