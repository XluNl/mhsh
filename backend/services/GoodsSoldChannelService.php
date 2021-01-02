<?php


namespace backend\services;


use backend\models\forms\GoodsSoldChannelForm;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use common\models\Goods;
use common\models\GoodsSoldChannel;
use common\utils\DateTimeUtils;
use Yii;

class GoodsSoldChannelService extends \common\services\GoodsSoldChannelService
{

    /**
     * 获取售卖渠道
     * @param $goodsId
     * @param $companyId
     * @return GoodsSoldChannelForm
     */
    public static function getSoldChannelForm($goodsId,$companyId){
        $soldChannelList = GoodsSoldChannel::find()->where(['goods_id'=>$goodsId,'company_id'=>$companyId])->all();
        $form = new GoodsSoldChannelForm();
        $form->sold_channel_ids = [];
        if (!empty($soldChannelList)){
            foreach ($soldChannelList as $v){
                $form->sold_channel_ids[] = $v['sold_channel_biz_id'];
            }
        }
        return $form;
    }

    /**
     * 保存售卖渠道
     * @param $model
     * @param $goodsId
     * @param $companyId
     * @return array
     */
    public static function addSoldChannel($model,$goodsId, $companyId){
        $soldChannelIds = $model->sold_channel_ids;
        $soldChannelType = $model->sold_channel_type;
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($res,$error) = parent::addGoodsSoldChannel($soldChannelType,$soldChannelIds,$goodsId,$companyId);
            BExceptionAssert::assertTrue($res,BBusinessException::create($error));
            $transaction->commit();
            return [true,''];
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return [false,'售卖渠道保存失败'.$e->getMessage()];
        }
    }
}