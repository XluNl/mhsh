<?php


namespace backend\services;


use backend\models\BackendCommon;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use common\models\Alliance;
use common\models\BusinessApply;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\Popularizer;
use common\utils\ModelUtils;
use Yii;
use yii\data\ActiveDataProvider;

class BusinessApplyService extends \common\services\BusinessApplyService
{


    /**
     * 操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operate($id,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[BusinessApply::ACTION_ACCEPT,BusinessApply::ACTION_DENY,BusinessApply::ACTION_DELETED]),$validateException);
        try{
            $operatorId = BackendCommon::getUserId();
            $operatorName = BackendCommon::getUserName();
            if ($commander==BusinessApply::ACTION_ACCEPT){
                self::acceptAndCreate($id,$company_id,$operatorId,$operatorName,"");
            }
            else if ($commander==BusinessApply::ACTION_DENY){
                self::denyAndUpdate($id,$company_id,$operatorId,$operatorName,"");
            }
            else if ($commander==BusinessApply::ACTION_DELETED){
                self::softDelete($id,$company_id,$operatorId,$operatorName,"");
            }
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            BackendCommon::showWarningInfo($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException);
        }
    }


    /**
     * 接受并创建
     * @param $id
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $operatorRemark
     * @throws BBusinessException
     * @throws \Exception
     */
    public static function acceptAndCreate($id,$company_id,$operatorId,$operatorName,$operatorRemark){
        $businessApply = self::getModel($id,$company_id);
        BExceptionAssert::assertNotNull($businessApply,BBusinessException::create("申请不存在"));
        BExceptionAssert::assertTrue($businessApply['action']==BusinessApply::ACTION_APPLY,BBusinessException::create("申请已处理，请勿重复处理"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if ($businessApply['type']==BusinessApply::APPLY_TYPE_POPULARIZER){
                self::createPopularizer($businessApply);
            }
            else if ($businessApply['type']==BusinessApply::APPLY_TYPE_DELIVERY){
                self::createDelivery($businessApply);
            }
            else if ($businessApply['type']==BusinessApply::APPLY_TYPE_HA){
                self::createAlliance($businessApply);
            }
            $updateCount = BusinessApply::updateAll(['action'=>BusinessApply::ACTION_ACCEPT,'operator_id'=>$operatorId,'operator_name'=>$operatorName,'operator_remark'=>$operatorRemark],['id'=>$id,'company_id'=>$company_id,'action'=>BusinessApply::ACTION_APPLY]);
            BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("申请信息更新失败"));
            $transaction->commit();
        }
        catch (BBusinessException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,BBusinessException::create($e->getMessage()));
        }
    }

    /**
     * 拒绝申请
     * @param $id
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $operatorRemark
     * @throws BBusinessException
     * @throws \Exception
     */
    public static function denyAndUpdate($id,$company_id,$operatorId,$operatorName,$operatorRemark){
        $businessApply = self::getModel($id,$company_id);
        BExceptionAssert::assertNotNull($businessApply,BBusinessException::create("申请不存在"));
        BExceptionAssert::assertTrue($businessApply['action']==BusinessApply::ACTION_APPLY,BBusinessException::create("申请已处理，请勿重复处理"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $updateCount = BusinessApply::updateAll(['action'=>BusinessApply::ACTION_DENY,'operator_id'=>$operatorId,'operator_name'=>$operatorName,'operator_remark'=>$operatorRemark],['id'=>$id,'company_id'=>$company_id,'action'=>BusinessApply::ACTION_APPLY]);
            BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("申请信息更新失败"));
            $transaction->commit();
        }
        catch (BBusinessException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,BBusinessException::create($e->getMessage()));
        }
    }

    /**
     * 删除申请
     * @param $id
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $operatorRemark
     */
    public static function softDelete($id, $company_id, $operatorId, $operatorName, $operatorRemark){
        $updateCount = BusinessApply::updateAll(['action'=>BusinessApply::ACTION_CANCEL,'status'=>CommonStatus::STATUS_DISABLED,'operator_id'=>$operatorId,'operator_name'=>$operatorName,'operator_remark'=>$operatorRemark],['id'=>$id,'company_id'=>$company_id,'action'=>BusinessApply::ACTION_APPLY,'status'=>CommonStatus::STATUS_ACTIVE]);
        BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("申请信息删除失败"));
    }



    /**
     * 创建推广团长
     * @param $businessApply
     */
    private static function createPopularizer($businessApply){
        $userInfoModel = UserInfoService::getActiveModel($businessApply['user_id'],true);
        BExceptionAssert::assertNotNull($userInfoModel,BBusinessException::create("用户未注册"));
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['em_phone'],'em_phone');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['wx_number'],'wx_number');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['realname'],'realname');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['occupation'],'occupation');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['province_id'],'province_id');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['city_id'],'city_id');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['county_id'],'county_id');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['community'],'community');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['address'],'address');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['lat'],'lat');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['lng'],'lng');
        $userInfoModel->is_popularizer = CommonStatus::STATUS_ACTIVE;
        BExceptionAssert::assertTrue($userInfoModel->save(),BBusinessException::create("用户信息更新失败"));

        $popularizerModels = PopularizerService::getActiveModelByUserId($businessApply['user_id'],$businessApply['company_id']);
        BExceptionAssert::assertEmpty($popularizerModels,BBusinessException::create("推广团长已注册"));
        $popularizer = new Popularizer();
        $popularizer->user_id = $businessApply['user_id'];
        $popularizer->status = CommonStatus::STATUS_ACTIVE;
        $popularizer->company_id = $businessApply['company_id'];
        $popularizer->phone = $userInfoModel['phone'];
        $popularizer->em_phone = $businessApply['em_phone'];
        $popularizer->wx_number = $businessApply['wx_number'];
        $popularizer->nickname = $businessApply['realname'];
        $popularizer->realname = $businessApply['realname'];
        $popularizer->occupation = $businessApply['occupation'];
        $popularizer->province_id = $businessApply['province_id'];
        $popularizer->city_id = $businessApply['city_id'];
        $popularizer->county_id = $businessApply['county_id'];
        $popularizer->community = $businessApply['community'];
        $popularizer->address = $businessApply['address'];
        $popularizer->lat = $businessApply['lat'];
        $popularizer->lng = $businessApply['lng'];
        BExceptionAssert::assertTrue($popularizer->save(),BBusinessException::create("推广团长注册失败"));
    }

    /**
     * 创建发货团长
     * @param $businessApply
     */
    private static function createDelivery($businessApply){
        $userInfoModel = UserInfoService::getActiveModel($businessApply['user_id'],true);
        BExceptionAssert::assertNotNull($userInfoModel,BBusinessException::create("用户未注册"));
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['em_phone'],'em_phone');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['wx_number'],'wx_number');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['realname'],'realname');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['head_img_url'],'head_img_url');
        $userInfoModel->is_delivery = CommonStatus::STATUS_ACTIVE;
        BExceptionAssert::assertTrue($userInfoModel->save(),BBusinessException::create("用户信息更新失败"));


        $delivery = new Delivery();
        $delivery->user_id = $businessApply['user_id'];
        $delivery->company_id = $businessApply['company_id'];
        $delivery->nickname = $businessApply['realname'];
        $delivery->realname = $businessApply['realname'];
        $delivery->wx_number = $businessApply['wx_number'];
        $delivery->province_id = $businessApply['province_id'];
        $delivery->city_id = $businessApply['city_id'];
        $delivery->county_id = $businessApply['county_id'];
        $delivery->community = $businessApply['community'];
        $delivery->address = $businessApply['address'];
        $delivery->lng = $businessApply['lng'];
        $delivery->lat = $businessApply['lat'];
        $delivery->status = CommonStatus::STATUS_ACTIVE;
        $delivery->phone = $userInfoModel['phone'];
        $delivery->em_phone = $businessApply['em_phone'];
        $delivery->min_amount_limit = 0;
        $delivery->allow_order = CommonStatus::STATUS_DISABLED;
        $delivery->type = Delivery::TYPE_COOPERATE;
        $delivery->head_img_url = $businessApply['head_img_url'];
        BExceptionAssert::assertTrue($delivery->save(),BBusinessException::create("配送团长注册失败"));
    }


    /**
     * 创建异业联盟商户
     * @param $businessApply BusinessApply
     */
    private static function createAlliance($businessApply){

        //校验是否还能再开新店铺
        list($checkError,$checkErrorMsg) = AllianceAuthService::checkCreateAlliance($businessApply['user_id']);
        BExceptionAssert::assertTrue($checkError,BBusinessException::create($checkErrorMsg));

        $userInfoModel = UserInfoService::getActiveModel($businessApply['user_id'],true);
        BExceptionAssert::assertNotNull($userInfoModel,BBusinessException::create("用户未注册"));
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['em_phone'],'em_phone');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['wx_number'],'wx_number');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['realname'],'realname');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['head_img_url'],'head_img_url');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['province_id'],'province_id');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['city_id'],'city_id');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['county_id'],'county_id');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['community'],'community');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['address'],'address');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['lat'],'lat');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['lng'],'lng');
        $userInfoModel->is_alliance = CommonStatus::STATUS_ACTIVE;
        BExceptionAssert::assertTrue($userInfoModel->save(),BBusinessException::create("用户信息更新失败"));


        $alliance = new Alliance();
        $alliance->user_id = $businessApply['user_id'];
        $alliance->company_id = $businessApply['company_id'];
        $alliance->nickname = $businessApply['nickname'];
        $alliance->realname = $userInfoModel['realname'];
        $alliance->wx_number = $businessApply['wx_number'];
        $alliance->province_id = $businessApply['province_id'];
        $alliance->city_id = $businessApply['city_id'];
        $alliance->county_id = $businessApply['county_id'];
        $alliance->community = $businessApply['community'];
        $alliance->address = $businessApply['address'];
        $alliance->lng = $businessApply['lng'];
        $alliance->lat = $businessApply['lat'];
        $alliance->status = Alliance::STATUS_PREPARE;
        $alliance->phone = $userInfoModel['phone'];
        $alliance->em_phone = $businessApply['em_phone'];
        $alliance->head_img_url = $businessApply['head_img_url'];
        $alliance->qualification_images = $businessApply['images'];
        $alliance->store_images = $businessApply['ext_images'];
        $alliance->type = Alliance::TYPE_NORMAL;
        $alliance->business_start = $businessApply['ext_v1'];
        $alliance->business_end = $businessApply['ext_v2'];
        $alliance->auth = Alliance::AUTH_STATUS_NO_AUTH;
        BExceptionAssert::assertTrue($alliance->save(),BBusinessException::create("异业联盟商户注册失败"));
    }

    /**
     * @param $dataProvider ActiveDataProvider
     * @return mixed
     */
    public static function renameImages($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        GoodsDisplayDomainService::batchRenameImageUrl($models,'images');
        GoodsDisplayDomainService::batchRenameImageUrl($models,'ext_images');
        $dataProvider->setModels($models);
        return $dataProvider;
    }
}