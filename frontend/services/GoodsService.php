<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 1:55
 */

namespace frontend\services;


use common\models\Alliance;
use common\models\GoodsConstantEnum;
use common\models\GoodsDetail;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class GoodsService
{
    public static function completeDetail($goodsModels, $companyId){
        if (empty($goodsModels)){
            return $goodsModels;
        }
        $goodsIds = ArrayHelper::getColumn($goodsModels,"goods_id");
        $goodsDetails = GoodsDetail::find()->where(['goods_id'=>$goodsIds,'company_id'=>$companyId])->all();
        $goodsAttributes = [
            /*['key'=>'产地','value'=>'杭州'],
            ['key'=>'保质期','value'=>'15'],
            ['key'=>'净含量','value'=>'24KG'],
            ['key'=>'储存方法','value'=>'常温18℃'],
            ['key'=>'品牌','value'=>'收到货后，请尽快食用，保证口感dsfsfsZ是非得失消毒柜保证口感dsfsfsZ是非得失消毒柜'],*/
        ];
        if (!empty($goodsModels)){
            $goodsDetails = ArrayHelper::index($goodsDetails,'goods_id');
            foreach ($goodsModels as $k=> $v){
                if (!key_exists($v['goods_id'],$goodsDetails)){
                    $goodsDetails[$v['goods_id']]['goods_detail'] ='';
                }
                if ($v['goods_owner']==GoodsConstantEnum::OWNER_SELF){
                    $goodsDetails[$v['goods_id']]['goods_detail'] .= Html::img(\Yii::$app->fileDomain->generateUrl("user-note-self.jpg?v=1"));
                }else if ($v['goods_owner']==GoodsConstantEnum::OWNER_HA){
                    $goodsDetails[$v['goods_id']]['goods_detail'] .= Html::img(\Yii::$app->fileDomain->generateUrl("user-note-alliance.jpg?v=1"));
                }

                $goodsModels[$k]['goods_detail'] = $goodsDetails[$v['goods_id']]['goods_detail'];
                $goodsModels[$k]['goods_attributes'] = $goodsAttributes;
            }
        }
        return $goodsModels;
    }

    /**
     * 组装优惠券活动信息
     * @param $company_id
     * @param $goodsList
     * @return array
     */
    public static function assembleCouponBatchInfo($company_id,$goodsList){
        if (empty($goodsList)){
            return [];
        }
        foreach ($goodsList as $k=>$v){
            if (key_exists('skus',$v)){
                $ids = ArrayHelper::getColumn($v['skus'],'sku_id');
                if (!empty($ids)){
                    $v['coupon_batches'] = CouponBatchService::getAvailableSkuCouponList($company_id,$ids);
                    $goodsList[$k] = $v;
                }
            }
        }
        return $goodsList;
    }

    /**
     * 补全购物车中商品数量
     * @param $userId
     * @param $skuList
     * @return array
     */
    public static function assembleCartNum($userId,$skuList){
        if (empty($skuList)){
            return [];
        }
        if (StringUtils::isBlank($userId)){
            $cartInfos = [];
        }
        else{
            $scheduleIds= ArrayHelper::getColumn($skuList,'schedule_id');
            $cartInfos = CartService::getCartByUserId($userId,$scheduleIds);
            $cartInfos = ArrayHelper::index($cartInfos,"schedule_id");
        }
        foreach ($skuList as $k=>$v){
            if (key_exists($v['schedule_id'],$cartInfos)){
                $v['num'] = $cartInfos[$v['schedule_id']]['num'];
            }
            else{
                $v['num'] = 0;
            }
            $skuList[$k] = $v;
        }
        return $skuList;
    }


    /**
     * 获取联盟信息
     * @param $companyId
     * @param $skuList
     * @return array|bool|Alliance|\yii\db\ActiveRecord|null
     */
    public static function checkAllianceStatus($companyId, $skuList) {
        ExceptionAssert::assertNotEmpty($skuList,StatusCode::createExp(StatusCode::GOODS_NOT_EXIST));
        $allianceId = null;
        foreach ($skuList as $k=>$v){
            if ($v['goods_owner']==GoodsConstantEnum::OWNER_HA){
                $allianceId = $v['goods_owner_id'];
                break;
            }
        }
        if (StringUtils::isBlank($allianceId)){
            return null;
        }
        $alliance = AllianceService::getActiveModel($allianceId,$companyId);
        ExceptionAssert::assertNotNull($alliance,StatusCode::createExp(StatusCode::ALLIANCE_NOT_EXIST));
        ExceptionAssert::assertTrue($alliance['status']==Alliance::STATUS_ONLINE,StatusCode::createExp(StatusCode::ALLIANCE_NOT_ONLINE));
        AllianceService::getDisplayVO($alliance);
        return $alliance;
    }

    /**
     * 补全联盟点信息
     * @param $skuList
     */
    public static function completeAlliance(&$skuList){
        if (!empty($skuList)){
            $allianceIds = [];
            foreach ($skuList as $k=>$v){
                if ($v['goods_owner']==GoodsConstantEnum::OWNER_HA){
                    $allianceIds[] = $v['goods_owner_id'];
                }
            }
            if (!empty($allianceIds)){
                $allianceIds = array_unique($allianceIds);
                $allianceModels = AllianceService::getAllActiveModel($allianceIds);
                AllianceService::batchGetDisplayVO($allianceModels);
                $allianceModels = ArrayUtils::index($allianceModels,'id');
                foreach ($skuList as $k=>$v){
                    if ($v['goods_owner']==GoodsConstantEnum::OWNER_HA&&key_exists($v['goods_owner_id'],$allianceModels)){
                        $v['alliance'] = $allianceModels[$v['goods_owner_id']];
                        $skuList[$k] = $v;
                    }
                }
            }
        }
    }



}