<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\models\Alliance;
use common\models\BusinessApply;
use common\models\CommonStatus;
use common\models\Customer;
use common\models\CustomerInvitation;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\Popularizer;
use common\models\User;
use common\models\UserInfo;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class UserInfoService extends \common\services\UserInfoService
{
    /**
     * 更新状态
     * @param $id
     * @param $commander
     * @param $validateException  RedirectParams
     */
    public static function operateStatus($id, $commander, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[CommonStatus::STATUS_DISABLED,CommonStatus::STATUS_ACTIVE]),$validateException->updateMessage("不支持的命令"));
        $count = UserInfo::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$id]);
        BExceptionAssert::assertTrue($count>0,$validateException->updateMessage("状态刷新失败"));
    }

    /**
     * 获取并校验
     * @param $id
     * @param $validateException
     * @param bool $model
     * @return array|bool|UserInfo|\yii\db\ActiveRecord|null
     */
    public static function requireModel($id,$validateException,$model = false){
        $model = self::getModel($id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 补全客户信息
     * @param $dataProvider ActiveDataProvider
     */
    public static function completeCustomerInfo(& $dataProvider){
        if (empty($dataProvider)){
            return;
        }
        $models = $dataProvider->getModels();
        $userInfoIds = ArrayUtils::getModelColumnWithoutNull('id',$models);
        $customerModels = CustomerService::getModelsByUserInfoId($userInfoIds);
        $customerModels = empty($customerModels)?[]:ArrayHelper::index($customerModels,'user_id');
        foreach ($models as $k=>$v){
            if (key_exists($v['id'],$customerModels)){
                $v->customer_id = $customerModels[$v['id']]['id'];
            }
            $models[$k] = $v;
        }
        $dataProvider->setModels($models);
    }

    /**
     * @param $id
     * @param $validateException RedirectParams
     */
    public static function deleteData($id,$validateException){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $userInfo = parent::getModel($id,true);
            BExceptionAssert::assertNotNull($userInfo,BBusinessException::create('用户信息不存在'));
            $customer = CustomerService::getModelByUserInfoId($userInfo['id']);
            if (!empty($customer)){
                $orders = OrderService::getOrdersModelByCustomerId($customer['id']);
                BExceptionAssert::assertEmpty($orders,BBusinessException::create('已下单用户不允许删除'));
                $invitations = CustomerInvitationService::getRelativeCustomer($customer['id']);
                $invitationIds = ArrayUtils::getColumnWithoutNull('id',$invitations);
                if (!empty($invitationIds)){
                    CustomerInvitation::deleteAll(['id'=>$invitationIds]);
                }
                Customer::deleteAll(['id'=>$customer['id']]);
            }
            $deliverys = DeliveryService::getModelByUserId($userInfo['id']);
            if (!empty($deliverys)){
                foreach ($deliverys as $delivery){
                    $orders = OrderService::getOrdersModelByDeliveryId($delivery['id']);
                    BExceptionAssert::assertEmpty($orders,BBusinessException::create("团点{$delivery['nickname']}下已有相关订单，不允许删除"));
                }
                $deliveryIds = ArrayUtils::getColumnWithoutNull('id',$deliverys);
                Delivery::deleteAll(['id'=>$deliveryIds]);
            }
            $popularizers = PopularizerService::getModelByUserId($userInfo['id']);
            if (!empty($popularizers)){
                foreach ($popularizers as $popularizer){
                    $orders = OrderService::getPopularizerRelativeOrder($popularizer['id']);
                    BExceptionAssert::assertEmpty($orders,BBusinessException::create("分享者{$popularizer['nickname']}下已有相关订单，不允许删除"));
                }
                $popularizerIds =  ArrayUtils::getColumnWithoutNull('id',$popularizers);
                Popularizer::deleteAll(['id'=>$popularizerIds]);
            }

            $alliances = AllianceService::getModelByUserId($userInfo['id']);
            if (!empty($alliances)){
                foreach ($alliances as $alliance){
                    $orders = OrderService::getOrders(GoodsConstantEnum::OWNER_HA,$alliance['id']);
                    BExceptionAssert::assertEmpty($orders,BBusinessException::create("联盟点{$alliance['nickname']}下已有相关订单，不允许删除"));
                }
                $allianceIds =  ArrayUtils::getColumnWithoutNull('id',$alliances);
                Alliance::deleteAll(['id'=>$allianceIds]);
            }
            BusinessApply::deleteAll(['user_id'=>$userInfo['id']]);
            UserInfo::deleteAll(['id'=>$id]);
            User::deleteAll(['user_info_id'=>$id]);
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }
    }

}