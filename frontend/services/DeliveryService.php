<?php


namespace frontend\services;


use common\models\CommonStatus;
use common\models\CustomerCompany;
use common\models\CustomerDelivery;
use common\models\Delivery;
use common\models\DeliveryType;
use common\models\GoodsConstantEnum;
use common\models\User;
use common\services\LocationService;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Query;

class DeliveryService extends \common\services\DeliveryService
{


    /**
     * 获取配送方案
     * @param $payAmount
     * @param $distance
     * @return array
     */
    public static function getAvailableFreight($payAmount, $distance)
    {
        $deliveryModel = FrontendCommon::requiredDelivery();
        $companyId = $deliveryModel['company_id'];
        $deliveryId = $deliveryModel['id'];
        $deliveryTypes = (new Query())->from(DeliveryType::tableName())->where([
            'delivery_id'=>$deliveryId,
            'company_id'=>$companyId,
            'status'=>CommonStatus::STATUS_ACTIVE
        ])->all();
        $availableTypes = [];
        foreach ($deliveryTypes as $deliveryType){
            if ($deliveryType['delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_SELF){
                $availableTypes[] = [
                    'type'=> $deliveryType['delivery_type'],
                    'name'=>GoodsConstantEnum::$deliveryTypeArr[GoodsConstantEnum::DELIVERY_TYPE_SELF],
                    'amount'=>$deliveryType['params'],
                ];
            }
            else if ($deliveryType['delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_HOME){
                $availableTypes[] = [
                    'type'=> $deliveryType['delivery_type'],
                    'name'=>GoodsConstantEnum::$deliveryTypeArr[GoodsConstantEnum::DELIVERY_TYPE_HOME],
                    'amount'=>$deliveryType['params'],
                ];
            }
            else if ($deliveryType['delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_EXPRESS){
                $availableTypes[] = [
                    'type'=> $deliveryType['delivery_type'],
                    'name'=>GoodsConstantEnum::$deliveryTypeArr[GoodsConstantEnum::DELIVERY_TYPE_EXPRESS],
                    'amount'=>$deliveryType['params'],
                ];
            }
        }
        return $availableTypes;
    }

    /**
     * 校验配送方案
     * @param $deliveryType
     * @param $deliveryId
     * @param $companyId
     * @param $payAmount
     * @param $distance
     * @return mixed
     */
    public static function getFreight($deliveryType, $deliveryId, $companyId, $payAmount, $distance)
    {
        $deliveryTypeModel = (new Query())->from(DeliveryType::tableName())->where([
            'delivery_id'=>$deliveryId,
            'company_id'=>$companyId,
            'delivery_type'=>$deliveryType
        ])->one();
        ExceptionAssert::assertNotNull($deliveryTypeModel,StatusCode::createExp(StatusCode::DELIVERY_TYPE_NOT_EXIST));
        ExceptionAssert::assertTrue($deliveryTypeModel['status']==CommonStatus::STATUS_ACTIVE,StatusCode::createExp(StatusCode::RECORD_ITEM_DISABLE));
        if ($deliveryTypeModel['delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_SELF){
            return $deliveryTypeModel['params'];
        }
        else if ($deliveryTypeModel['delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_HOME){
            return $deliveryTypeModel['params'];
        }
        else if ($deliveryTypeModel['delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_EXPRESS){
            return $deliveryTypeModel['params'];
        }
        else{
            ExceptionAssert::assertTrue(false,StatusCode::createExp(StatusCode::DELIVERY_TYPE_NOT_EXIST));
        }
    }



    /**
     * 获取当前配送点信息，可能为空
     * @param $lat
     * @param $lng
     * @return array|null
     */
    public static function getCurrent($lat,$lng){
        $deliveryId =  FrontendCommon::getDeliveryId();
        if (StringUtils::isBlank($deliveryId)){
            return null;
        }
        $deliveryModel = DeliveryService::getActiveModel($deliveryId);
        if (empty($deliveryModel)){
            return null;
        }
        $deliveryModel['distance'] = LocationService::getDistance($deliveryModel['lng'],$deliveryModel['lat'],$lng,$lat);
        $deliveryModel['distance_text'] = LocationService::resolveDistance($deliveryModel['distance']);
        return $deliveryModel;
    }


    /**
     * 获取当前设定的配送点
     * @param $userModel User
     * @return |null
     */
    public static function getSelectedDeliveryId($userModel){
        if (!StringUtils::isBlank($userModel->user_info_id)){
            $customerDelivery = (new Query())->from(CustomerDelivery::tableName())->where(['user_id'=>$userModel->user_info_id])->one();
            if (empty($customerDelivery)){
                return null;
            }
            return $customerDelivery['delivery_id'];
        }
        else{
            return $userModel->delivery_id;
        }
    }

    /**
     * 修改默认配送点
     * @param $userId
     * @param $deliveryId
     */
    public static function changeSelectedDeliveryId($userId,$deliveryId){
        $customerDelivery = CustomerDelivery::find()->where(['user_id'=>$userId])->one();
        if (empty($customerDelivery)){
            $customerDelivery = new CustomerDelivery();
            $customerDelivery->user_id = $userId;
        }
        $customerDelivery->delivery_id = $deliveryId;
        ExceptionAssert::assertTrue($customerDelivery->save(),StatusCode::createExp(StatusCode::STATUS_SELECTED_DELIVERY_ERROR));
    }

    /**
     * 更改配送点
     * @param $userModel
     * @param $deliveryId
     * @throws BusinessException
     * @throws \yii\db\Exception
     */
    public static function changeSelectedDelivery($userModel,$deliveryId){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if (!StringUtils::isBlank($userModel->user_info_id)){
                $customerDelivery = CustomerDelivery::find()->where(['user_id'=>$userModel->user_info_id])->one();
                if (empty($customerDelivery)){
                    $customerDelivery = new CustomerDelivery();
                    $customerDelivery->user_id = $userModel->user_info_id;
                }
                $customerDelivery->delivery_id = $deliveryId;
                ExceptionAssert::assertTrue($customerDelivery->save(),StatusCode::createExp(StatusCode::STATUS_SELECTED_DELIVERY_ERROR));
            }
            // 用户对应公司记录（不存在添加）
            $delivery = Delivery::find()->where(['id'=>$deliveryId])->one();
            ExceptionAssert::assertNotNull($delivery,StatusCode::createExp(StatusCode::STATUS_SELECTED_DELIVERY_ERROR));

            $customerCompany = CustomerCompany::find()->where(['user_id'=>$userModel->id, 'company_id'=>$delivery->company_id])->one();
            if (empty($customerCompany)){
                $customerCompany = new CustomerCompany();
                $customerCompany->user_id = $userModel->id;
                $customerCompany->company_id = $delivery->company_id;
                ExceptionAssert::assertTrue($customerCompany->save(),StatusCode::createExp(StatusCode::STATUS_SELECTED_DELIVERY_ERROR));
            }
            User::updateAll(['delivery_id'=>$deliveryId],['id'=>$userModel->id]);
            $transaction->commit();
        } catch (BusinessException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false, StatusCode::createExp(StatusCode::STATUS_SELECTED_DELIVERY_ERROR));
        }
    }

}