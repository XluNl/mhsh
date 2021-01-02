<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\models\GoodsSkuAlliance;
use common\services\AllianceAuthService;
use common\utils\DateTimeUtils;
use Yii;
use yii\data\ActiveDataProvider;

class GoodsSkuAllianceService extends  \common\services\GoodsSkuAllianceService
{
    /**
     * @param $dataProvider ActiveDataProvider
     */
    public static function completeInfos(&$dataProvider)
    {
        if (empty($dataProvider)){
            return;
        }
        $models = $dataProvider->getModels();
        $models = parent::batchRenamePic($models);

        $dataProvider->setModels($models);
    }

    /**
     *
     * @param $id
     * @param $commander
     * @param $auditNote
     * @param $companyId
     * @param $operatorId
     * @param $operatorName
     * @param $validateException  BBusinessException
     */
    public static function operate($id, $commander, $auditNote, $companyId, $operatorId, $operatorName, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[GoodsSkuAlliance::AUDIT_STATUS_ACCEPT,GoodsSkuAlliance::AUDIT_STATUS_DENY]),$validateException);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $count = GoodsSkuAlliance::updateAll([
                'audit_status'=>$commander,
                'audit_result'=>$auditNote,
                'operator_id'=>$operatorId,
                'operator_name'=>$operatorName,
                'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())
            ],['id'=>$id,'company_id'=>$companyId,'audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_WAITING]);
            BExceptionAssert::assertTrue($count>0,BBusinessException::create("审核状态更新失败"));
            $goodsSkuAlliance = GoodsSkuAllianceService::getModel($id,null,null,$companyId,false);
            BExceptionAssert::assertNotNull($goodsSkuAlliance,BBusinessException::create("商品审核条目不存在"));
            //联盟商品默认发布并上线
            self::publishAllianceGoodsAndSkuB($goodsSkuAlliance,$companyId);
            $transaction->commit();

        }
        catch (\Exception $e){
            $transaction->rollBack();
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }
    }

    /**
     * 联盟商品默认发布并上线
     * @param $goodsSkuAlliance
     * @param $companyId
     */
    private static function publishAllianceGoodsAndSkuB($goodsSkuAlliance, $companyId){
        if ($goodsSkuAlliance['goods_owner_type']!=GoodsConstantEnum::OWNER_HA){
            return;
        }
        $alliance = AllianceService::getActiveModel($goodsSkuAlliance['goods_owner_id'],$companyId);
        BExceptionAssert::assertNotNull($alliance,BBusinessException::create("联盟商户不存在"));
        list($checkError,$checkErrorMsg) = AllianceAuthService::checkCreateGoods($alliance,$goodsSkuAlliance['goods_id']);
        BExceptionAssert::assertTrue($checkError,BBusinessException::create($checkErrorMsg));
        list($result,$error) = parent::publishAllianceGoodsAndSku($goodsSkuAlliance['id'],$goodsSkuAlliance['goods_owner_type'],$alliance['id'],$goodsSkuAlliance,$companyId);
        BExceptionAssert::assertTrue($result,BBusinessException::create($error));
        $goodsSkuAlliance = GoodsSkuAllianceService::getModel($goodsSkuAlliance['id'],null,null,$companyId,false);
        $count = Goods::updateAll(['goods_status'=>GoodsConstantEnum::STATUS_UP],['id'=>$goodsSkuAlliance['goods_id'],'company_id'=>$companyId]);
        $count = GoodsSku::updateAll(['sku_status'=>GoodsConstantEnum::STATUS_UP],['id'=>$goodsSkuAlliance['sku_id'],'goods_id'=>$goodsSkuAlliance['goods_id'],'company_id'=>$companyId]);
    }

}