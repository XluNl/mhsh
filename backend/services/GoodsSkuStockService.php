<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use common\models\GoodsSku;
use common\models\GoodsSkuStock;
use Yii;

class GoodsSkuStockService extends \common\services\GoodsSkuStockService
{
    /**
     * @param $model GoodsSkuStock
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function operateGoodsSkuStock($model){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            BExceptionAssert::assertTrue($model->save(),BBusinessException::create("录入失败"));
            $num= $model->num;
            if (key_exists($model->type,GoodsSkuStock::$outArr)){
                $num = -$num;
            }
            $updateCount= GoodsSku::updateAllCounters(['sku_stock'=>$num],['company_id'=>$model->company_id,'id'=>$model->sku_id]);
            BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("更新sku库存失败"));
            $transaction->commit();
            return true;
        }
        catch (BBusinessException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
        }
        return false;
    }









}