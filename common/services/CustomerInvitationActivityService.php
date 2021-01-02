<?php


namespace common\services;


use common\models\BizTypeEnum;
use common\models\BonusBatchDrawLog;
use common\models\CommonStatus;
use common\models\CustomerInvitation;
use common\models\CustomerInvitationActivity;
use common\models\CustomerInvitationActivityPrize;
use common\models\DistributeBalanceItem;
use common\models\RoleEnum;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;

class CustomerInvitationActivityService
{
    /**
     * 根据ID获取
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|CustomerInvitationActivity|\yii\db\ActiveRecord|null
     */
    public static function getModel($id,$company_id=null, $model = false){
        $conditions = ['id' => $id];
        if (StringUtils::isNotBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return CustomerInvitationActivity::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(CustomerInvitationActivity::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 查找活动
     * @param $company_id
     * @param $nowTime
     * @param $type
     * @return array|bool|null
     */
    public static function getActiveModelOne($company_id,$nowTime,$type){
        $conditions = [
            'and',
            [
                'company_id' => $company_id,
                'status' => CommonStatus::STATUS_ACTIVE,
                'type'=>$type,
            ],
            ['<=', 'show_start_time', $nowTime],
            ['>=', 'show_end_time', $nowTime]
        ];
        $result = (new Query())->from(CustomerInvitationActivity::tableName())->where($conditions)->one();
        return $result===false?null:$result;
    }



    /**
     * 预统计活动发放情况
     * @param $activityId
     * @param $companyId
     * @param null $customerId
     * @return array
     */
    public static function preStatistic($activityId,$companyId,$customerId=null){
        $activityModel = self::getModel($activityId,$companyId,false);
        if (empty($activityModel)){
            return [false,'活动不存在'];
        }
        if ($activityModel['settle_status']){
            return [false,'活动已结算，请查看结算数据'];
        }
        $activityPrizeModels = CustomerInvitationActivityPrizeService::getActiveModelsByActivityId($activityId,$companyId);
        $oneLevelOrderStatistic = OrderService::activeOneLevelOrderStatistic($companyId,$activityModel['activity_start_time'],$activityModel['activity_end_time'],$customerId);
        $oneLevelOrderStatistic = empty($oneLevelOrderStatistic)?[]:ArrayHelper::index($oneLevelOrderStatistic,'customer_id');
        self::validateInvitation($oneLevelOrderStatistic);
        $twoLevelOrderStatistic = OrderService::activeTwoLevelOrderStatistic($companyId,$activityModel['activity_start_time'],$activityModel['activity_end_time'],$customerId);
        $twoLevelOrderStatistic = empty($twoLevelOrderStatistic)?[]:ArrayHelper::index($twoLevelOrderStatistic,'customer_id');
        self::validateInvitation($twoLevelOrderStatistic);
        $sumPrizes = [];
        $invitationModels = CustomerInvitationService::statisticOneTwoLevel($activityModel['activity_start_time'],$activityModel['activity_end_time'],$customerId);
        foreach ($invitationModels as $k=>$v){
            if (key_exists($v['customer_id'],$oneLevelOrderStatistic)){
                $oneLevelIds = empty($oneLevelOrderStatistic[$v['customer_id']]['children'])?[]:ArrayHelper::getColumn($oneLevelOrderStatistic[$v['customer_id']]['children'],'child_customer_id');
                $twoLevelIds = empty($twoLevelOrderStatistic[$v['customer_id']]['children'])?[]:ArrayHelper::getColumn($twoLevelOrderStatistic[$v['customer_id']]['children'],'child_customer_id');
                $ids1 = self::getConnectIds($v['children']);

                //$v['invitation_order_count'] =count(array_intersect($oneLevelIds,$ids1));
                $v['invitation_order_count'] =self::calcValidateInvitation($ids1,$v['customer_id'],$oneLevelOrderStatistic);
                $v['invitation_children_order_count'] = 0 ;
                foreach ($v['children'] as $kk=> $vv){

                    $ids2 = self::getConnectIds($vv['children']);

                    //$vv['invitation_order_count'] =count(array_intersect($twoLevelIds,$ids2));
                    $vv['invitation_order_count'] =self::calcValidateInvitation($ids2,$v['customer_id'],$twoLevelOrderStatistic);
                    $v['invitation_children_order_count'] += $vv['invitation_order_count'];

                    if (key_exists($vv['child_customer_id'],$oneLevelOrderStatistic[$v['customer_id']]['children'])){
                        $oneLevelChildOrderStatistic = $oneLevelOrderStatistic[$v['customer_id']]['children'][$vv['child_customer_id']];
                        $vv['child_customer_order_count'] = $oneLevelChildOrderStatistic['child_order_count'];
                        $vv['child_customer_order_amount'] = $oneLevelChildOrderStatistic['child_order_amount'];
                        $vv['validate'] = $oneLevelChildOrderStatistic['validate'];
                    }
                    else{
                        $vv['child_customer_order_count'] = 0;
                        $vv['child_customer_order_amount'] = 0;
                        $vv['validate'] = CustomerInvitationActivity::VALIDATE_FALSE;
                    }
                    //组装二级邀请数据
                    self::assembleTwoLevelOrder($v, $twoLevelOrderStatistic, $vv, $kkk, $vvv, $twoLevelChildOrderStatistic);


                    $v['children'][$kk] = $vv;
                }
                $v['children'] = ArrayUtils::sortByTwoFiled($v['children'],'child_customer_order_count',SORT_DESC,'child_customer_order_amount',SORT_DESC);
            }
            else{
                $v['invitation_order_count'] = 0;
                $v['invitation_children_order_count'] = 0;
                foreach ($v['children'] as $kk=> $vv){
                    $vv['invitation_order_count'] = 0;
                    $vv['child_customer_order_count'] = 0;
                    $vv['child_customer_order_amount'] = 0;
                    $vv['validate'] = CustomerInvitationActivity::VALIDATE_FALSE;
                    //组装二级邀请数据
                    self::assembleTwoLevelOrder($v, $twoLevelOrderStatistic, $vv, $kkk, $vvv, $twoLevelChildOrderStatistic);

                    $v['children'][$kk] = $vv;
                }
            }
            $v['invitation_count'] =(integer)$v['invitation_count'];
           // unset( $v['invitation_customer_ids']);
            $v['prizes'] = [];
            foreach ($activityPrizeModels as $p){
                if ($p['level_type']==CustomerInvitationActivityPrize::LEVEL_TYPE_ONE&&$p['range_start']<=$v['invitation_order_count']&&$p['range_end']>=$v['invitation_order_count']
                ||$p['level_type']==CustomerInvitationActivityPrize::LEVEL_TYPE_TWO&&$p['range_start']<=$v['invitation_children_order_count']&&$p['range_end']>=$v['invitation_children_order_count']){
                    $v['prizes'][] = [
                        'id'=>$p['id'],
                        'type'=>$p['type'],
                        'level_type'=>$p['level_type'],
                        'batch_no'=>$p['batch_no'],
                        'name'=>$p['name'],
                        'num'=>$p['num'],
                        'num_text'=>CustomerInvitationActivityPrizeService::exportNumText($p['type'],$p['num'])
                    ];
                    if (!key_exists($p['id'],$sumPrizes)){
                        $sumPrizes[$p['id']] = [
                            'id'=>$p['id'],
                            'name'=>$p['name'],
                            'batch_no'=>$p['batch_no'],
                            'type'=>$p['type'],
                            'level_type'=>$p['level_type'],
                            'num'=>0,
                        ];
                    }
                    $sumPrizes[$p['id']]['num'] +=$p['num'];
                }
            }
            $invitationModels[$k] = $v;
        }
        $invitationModels = ArrayUtils::sortByTwoFiled($invitationModels,'invitation_order_count',SORT_DESC,'invitation_children_order_count',SORT_DESC);
        foreach ($sumPrizes as $k=>$v){
            $v['num_text'] = CustomerInvitationActivityPrizeService::exportNumText($v['type'],$v['num']);
            $sumPrizes[$k] = $v;
        }

        return [true,
            [
                'invitationModels'=>$invitationModels,
                'sumPrizes'=>$sumPrizes,
                'activityModel'=>$activityModel
            ]
        ];
    }


    /**
     * 活动结算
     * @param $activityId
     * @param $companyId
     * @param $operatorId
     * @param $operatorName
     * @param string $remark
     * @return array
     */
    public static function settleActivity($activityId,$companyId,$operatorId,$operatorName,$remark=''){
        list($result,$data) = self::preStatistic($activityId,$companyId);
        if (!$result){
            return [$result,$data];
        }
        $invitationModels = $data['invitationModels'];
        $activityModel = $data['activityModel'];
        $remark = '拉新活动：'.$activityModel['name'].',备注:'.$remark;
        $successPrizes=[];
        $failedPrizes=[];
        foreach ($invitationModels as $k=>$v){
            $v['is_draw'] = true;
            if (!empty($v['prizes'])){
                foreach ($v['prizes'] as $kk=>$vv){
                    list($res,$error) = CustomerInvitationActivityPrizeService::reducePrize($vv['id'],$activityId,$vv['num']);
                    if (!$res){
                        self::sumPrizes($vv, $v,$failedPrizes,false,$error);
                    }
                    else{
                        if ($vv['type']==CustomerInvitationActivityPrize::TYPE_COUPON){
                            list($res,$error)=CouponBatchService::drawCoupon($companyId,$v['customer_id'],$vv['batch_no'],$vv['num'],false,$operatorId,$operatorName,RoleEnum::ROLE_ADMIN,$remark);
                            if ($res){
                                self::sumPrizes($vv,$v, $successPrizes,true);
                            }
                            else{
                                list($res,$error2) = CustomerInvitationActivityPrizeService::recoveryPrize($vv['id'],$activityId,$vv['num']);
                                if (!$res){
                                    return [false,$error2];
                                }
                                self::sumPrizes($vv, $v,$failedPrizes,false,$error);
                            }

                        }
                        else if ($vv['type']==CustomerInvitationActivityPrize::TYPE_BONUS){
                            list($res,$error) = BonusBatchService::drawBonus($companyId,BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,$v['customer_id'],BonusBatchDrawLog::DRAW_TYPE_INVITATION_ACTIVITY,$activityId,DistributeBalanceItem::TYPE_CUSTOMER_INVITATION_ACTIVITY_BONUS,$activityId,$vv['batch_no'],$vv['num'],$operatorId,$operatorName,RoleEnum::ROLE_ADMIN,$remark);
                            if ($res){
                                self::sumPrizes($vv,$v, $successPrizes,true);
                            }
                            else{
                                list($res,$error2) = CustomerInvitationActivityPrizeService::recoveryPrize($vv['id'],$activityId,$vv['num']);
                                if (!$res){
                                    return [false,$error2];
                                }
                                self::sumPrizes($vv, $v,$failedPrizes,false,$error);
                            }
                        }
                        else if ($vv['type']==CustomerInvitationActivityPrize::TYPE_OTHER){
                            self::sumPrizes($vv, $v,$successPrizes,true);
                        }
                        else{
                            self::sumPrizes($vv, $v,$failedPrizes,false,"未知优惠类型{$vv['type']}");
                        }
                    }
                    $v['prizes'][$kk]=$vv;
                }
            }
            list($res,$error) =  CustomerInvitationActivityResultService::addResult($activityId,$v['customer_id'],$v['customer_name'],$v['customer_phone'],$v['invitation_count'],$v['invitation_order_count'],$v['invitation_children_count'],$v['invitation_children_order_count'],$v['children'],$v['prizes']);
            if (!$res){
                return [false,$error];
            }
            $invitationModels[$k] = $v;
            CustomerService::completeCustomerInfo($invitationModels,'customer_id');
        }

        list($res,$error) =  self::updateActivitySettleStatus($activityId,$companyId,$operatorId,$operatorName,$activityModel['version']);
        if (!$res){
            return [false,$error];
        }

        $invitationModels = ArrayUtils::sortByTwoFiled($invitationModels,'invitation_order_count',SORT_DESC,'invitation_children_order_count',SORT_DESC);
        $successPrizes = self::setPrizesNumText($successPrizes);
        $failedPrizes = self::setPrizesNumText($failedPrizes);
        return [true,[
            'activityModel'=>$activityModel,
            'successPrizes'=>$successPrizes,
            'failedPrizes'=>$failedPrizes,
            'invitationModels'=>$invitationModels,
        ]];
    }

    /**
     * @param $vv
     * @param $v
     * @param array $prizes
     * @param $result
     * @param string $error
     */
    public static function sumPrizes(&$vv,&$v, array &$prizes,$result,$error='')
    {
        if (!key_exists($vv['id'], $prizes)) {
            $prizes[$vv['id']] = [
                'id' => $vv['id'],
                'name' => $vv['name'],
                'batch_no' => $vv['batch_no'],
                'type' => $vv['type'],
                'level_type'=>$vv['level_type'],
                'num' => 0,
            ];
        }
        $prizes[$vv['id']]['num'] += $vv['num'];
        $vv['is_draw'] = $result;
        if ($result===false){
            $vv['draw_error'] =$error;
            $v['is_draw'] = false;
        }
    }

    /**
     * @param array $successPrizes
     * @return array
     */
    public static function setPrizesNumText(array $successPrizes)
    {
        foreach ($successPrizes as $k => $v) {
            $v['num_text'] = CustomerInvitationActivityPrizeService::exportNumText($v['type'], $v['num']);
            $successPrizes[$k] = $v;
        }
        return $successPrizes;
    }

    /**
     * 更新活动结算状态
     * @param $id
     * @param $companyId
     * @param $operatorId
     * @param $operatorName
     * @param $version
     * @return array
     */
    private static function updateActivitySettleStatus($id,$companyId,$operatorId,$operatorName,$version){
        $updateCount = CustomerInvitationActivity::updateAll([
            'version'=>$version+1,
            'settle_status'=>CustomerInvitationActivity::SETTLE_STATUS_DEAL,
            'settle_operator_id'=>$operatorId,
            'settle_operator_name'=>$operatorName,
            'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())
        ],['version'=>$version,'id'=>$id,'company_id'=>$companyId,'settle_status'=>CustomerInvitationActivity::SETTLE_STATUS_UN_DEAL]);
        if ($updateCount>0){
            return [true,'邀请活动结算状态更新成功'];
        }
        else{
            return [false,'邀请活动结算状态更新失败'];
        }
    }

    /**
     * 组装二级邀请数据
     * @param $v
     * @param array $twoLevelOrderStatistic
     * @param $vv
     * @param $kkk
     * @param $vvv
     * @param $twoLevelChildOrderStatistic
     */
    public static function assembleTwoLevelOrder($v, array $twoLevelOrderStatistic, &$vv, &$kkk, &$vvv, &$twoLevelChildOrderStatistic)
    {
        if (key_exists($v['customer_id'], $twoLevelOrderStatistic)) {
            if (!empty($vv['children'])) {
                foreach ($vv['children'] as $kkk => $vvv) {
                    if (key_exists($vvv['child_customer_id'], $twoLevelOrderStatistic[$v['customer_id']]['children'])) {
                        $twoLevelChildOrderStatistic = $twoLevelOrderStatistic[$v['customer_id']]['children'][$vvv['child_customer_id']];
                        $vvv['child_customer_order_count'] = $twoLevelChildOrderStatistic['child_order_count'];
                        $vvv['child_customer_order_amount'] = $twoLevelChildOrderStatistic['child_order_amount'];
                        $vvv['validate'] = $twoLevelChildOrderStatistic['validate'];
                    }
                    else{
                        $vvv['child_customer_order_count'] = 0;
                        $vvv['child_customer_order_amount'] = 0;
                        $vvv['validate'] = CustomerInvitationActivity::VALIDATE_FALSE;
                    }
                    $vv['children'][$kkk] = $vvv;
                }
            }
        } else {
            $vv['invitation_order_count'] = 0;
            foreach ($vv['children'] as $kkk => $vvv) {
                $vvv['child_customer_order_count'] = 0;
                $vvv['child_customer_order_amount'] = 0;
                $vvv['validate'] = CustomerInvitationActivity::VALIDATE_FALSE;
                $vv['children'][$kkk] = $vvv;
            }
        }
    }

    /**
     * 获取有效链接
     * @param $children
     * @return array
     */
    public static function getConnectIds($children){
        if (empty($children)){
            return [];
        }
        $ids = [];
        foreach ($children as $child){
            if ($child['child_customer_is_connect']==CustomerInvitation::IS_CONNECT_TRUE){
                $ids[] = $child['child_customer_id'];
            }
        }
        return $ids;
    }


    /**
     * 判定是否有效
     * @param $orderStatistic
     */
    private static function validateInvitation(&$orderStatistic){
        if (empty($orderStatistic)){
            return;
        }
        foreach ($orderStatistic as $k=>$v){
            if (key_exists('children',$v)){
                foreach ($v['children'] as $kk=>$vv){
                    if ($vv['child_order_count']>=Yii::$app->params['customer.invitation.activity.order.count']){
                        $vv['validate'] = CustomerInvitationActivity::VALIDATE_TRUE;
                    }
                    else{
                        $vv['validate'] = CustomerInvitationActivity::VALIDATE_FALSE;
                    }
                    $v['children'][$kk] = $vv;
                }
            }
            $orderStatistic[$k]=$v;
        }
    }


    /**
     * 计算有效下单人数
     * @param $ids
     * @param $customerId
     * @param $oneStatistic
     * @return int
     */
    private static function calcValidateInvitation($ids,$customerId,$oneStatistic){
        if (empty($ids)||!key_exists($customerId,$oneStatistic)||!key_exists('children',$oneStatistic[$customerId])){
            return 0;
        }
        $count = 0;
        foreach ($oneStatistic[$customerId]['children'] as $k=>$v){
            if ($v['validate']===CustomerInvitationActivity::VALIDATE_TRUE&&in_array($k,$ids)){
                $count++;
            }
        }
        return $count;
    }

}