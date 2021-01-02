<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 1:55
 */

namespace alliance\services;


use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuAlliance;
use common\services\AllianceAuthService;
use common\utils\CopyUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;

class GoodsSkuAllianceService extends \common\services\GoodsSkuAllianceService
{
    /**
     * 单个查询
     * @param $id
     * @param $allianceId
     * @param $companyId
     * @param bool $model
     * @return array|bool|\common\services\GoodsSkuAllianceService|mixed|null
     */
    public static function getGoodsInfo($id,$allianceId,$companyId,$model=false){
        $model = parent::getModel($id,GoodsConstantEnum::OWNER_HA,$allianceId,$companyId,$model);
        ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::GOODS_SKU_ALLIANCE_NOT_EXIST));
        parent::showPrice($model);
        parent::showStatusText($model);
        return $model;
    }

    /**
     * 列表
     * @param $allianceId
     * @param $companyId
     * @param $auditStatus
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
    public static function getGoodsInfoList($allianceId,$companyId,$auditStatus,$pageNo,$pageSize){
        $models = parent::getModels(GoodsConstantEnum::OWNER_HA,$allianceId,$companyId,$auditStatus,$pageNo,$pageSize);
        CopyUtils::batchCopyAttr($models,'sku_img','sku_img_text');
        $models = GoodsDisplayDomainService::batchRenameImageUrl($models,'sku_img_text');
        CopyUtils::batchCopyAttr($models,'goods_img','goods_img_text');
        $models = GoodsDisplayDomainService::batchRenameImageUrl($models,'goods_img_text');
        parent::batchShowPrice($models);
        parent::batchShowStatusText($models);
        return $models;
    }

    /**
     * 提交审核
     * @param $id
     * @param $allianceId
     * @param $companyId
     */
    public static function submitAudit($id,$allianceId,$companyId){
        $count = GoodsSkuAlliance::updateAll(['audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_WAITING,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$id,'goods_owner_type'=>GoodsConstantEnum::OWNER_HA,'goods_owner_id'=>$allianceId,'company_id'=>$companyId,'audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_EDIT]);
        ExceptionAssert::assertTrue($count>0,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"当前状态不支持发起审核"));
    }

    /**
     * 撤回
     * @param $id
     * @param $allianceId
     * @param $companyId
     */
    public static function withdraw($id,$allianceId,$companyId){
        $count = GoodsSkuAlliance::updateAll(['audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_EDIT,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$id,'goods_owner_type'=>GoodsConstantEnum::OWNER_HA,'goods_owner_id'=>$allianceId,'company_id'=>$companyId,'audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_WAITING]);
        ExceptionAssert::assertTrue($count>0,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"当前状态不支持撤回"));
    }

    /**
     * 发布
     * @param $id
     * @param $alliance
     * @param $companyId
     * @throws BusinessException
     */
    public static function publish($id,$alliance,$companyId){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //校验是否还能发布商品
            $model = self::getModel($id,GoodsConstantEnum::OWNER_HA,$alliance['id'],$companyId,false);
            ExceptionAssert::assertNotNull($model,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,'商品审核条目不存在'));
            list($checkError,$checkErrorMsg) = AllianceAuthService::checkCreateGoods($alliance,$model['goods_id']);
            ExceptionAssert::assertTrue($checkError,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$checkErrorMsg));
            list($result,$error) = parent::publishAllianceGoodsAndSku($id,$model['goods_owner_type'],$alliance['id'],$model,$companyId);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$error));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$e->getMessage()));
        }
    }

    public static function completeStock(&$model){
        if (empty($model)){
            return;
        }
        $model['sku_sold'] = 0;
        if (StringUtils::isNotBlank($model['sku_id'])){
            $sku = GoodsSkuService::getSkuInfo($model['sku_id'],$model['goods_owner_id'],$model['company_id']);
            if (!empty($sku)){
                $model['sku_sold'] = $sku['sku_sold'];
                $model['sku_stock'] = $sku['sku_stock'];
            }
        }
    }

}