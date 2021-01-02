<?php


namespace common\services;


use common\models\Common;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;

class GoodsDisplayDomainService
{
    /**
     * 设置状态+图片域名+默认图片
     * @param $skuList
     * @return array
     */
    public static function assembleStatusAndImageAndExceptTime($skuList){
        $skuList = GoodsDisplayDomainService::batchDefineStatusDisplayVO($skuList);
        $skuList = self::assembleImage($skuList);
        $skuList = self::batchDefineExpectArriveTimeText($skuList);
        return $skuList;
    }

    /**
     * 批量设置状态+图片域名+默认图片
     * @param $skuList
     * @return array
     */
    public static function assembleImage($skuList){
        $skuList = GoodsDisplayDomainService::batchRenameImageUrl($skuList,'sku_img');
        $skuList = GoodsDisplayDomainService::batchRenameImageUrl($skuList,'goods_img');
        $skuList = GoodsDisplayDomainService::batchRenameImageUrl($skuList,'goods_images');
        $skuList = GoodsDisplayDomainService::batchRenameImageUrl($skuList,'goods_video');
        $skuList = GoodsDisplayDomainService::batchReplaceIfNotExist($skuList,'sku_img','goods_img');
        $skuList = GoodsDisplayDomainService::batchSetDefaultIfNotExist($skuList,'sku_img');
        $skuList = GoodsDisplayDomainService::batchSetDefaultIfNotExist($skuList,'goods_img');
        return $skuList;
    }

    /**
     * 设置状态+图片域名+默认图片
     * @param $sku
     * @return array|mixed
     */
    public static function assembleImageOne($sku){
        $sku = GoodsDisplayDomainService::renameImageUrl($sku,'sku_img');
        $sku = GoodsDisplayDomainService::renameImageUrl($sku,'goods_img');
        $sku = GoodsDisplayDomainService::renameImageUrl($sku,'goods_images');
        $sku = GoodsDisplayDomainService::renameImageUrl($sku,'goods_video');
        $sku = GoodsDisplayDomainService::replaceIfNotExist($sku,'sku_img','goods_img');
        $sku = GoodsDisplayDomainService::setDefaultIfNotExist($sku,'sku_img');
        $sku = GoodsDisplayDomainService::setDefaultIfNotExist($sku,'goods_img');
        return $sku;
    }


    /**
     * 批量重写图片绝对路径
     * @param $list  [列表]
     * @param $attr [属性名称]
     * @param $dist
     * @return array
     */
    public static function batchRenameImageUrl($list,$attr,$dist=null){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=> $v){
            $list[$k] = self::renameImageUrl($v,$attr,$dist);
        }
        return $list;
    }

    /**
     * 重写图片绝对路径
     * @param $obj  [对象]
     * @param $attr  [属性名称]
     * @param $dist
     * @return mixed
     */
    public static function renameImageUrl($obj,$attr,$dist=null){
        if (!StringUtils::isBlank(ArrayUtils::getArrayValue($attr,$obj,''))){
            $images =  explode(',',$obj[$attr]);
            foreach ($images as $k=>$v){
                $images[$k] = Common::generateAbsoluteUrl($v);
            }
            if (StringUtils::isNotBlank($dist)){
                $obj[$dist] = implode(",", $images);
            }
            else{
                $obj[$attr] = implode(",", $images);
            }
        }
        return $obj;
    }

    /**
     * 批量 图片增加域名，如果不存在则设置默认值
     * @param $list
     * @param $attr
     * @return array
     */
    public static function batchRenameImageUrlOrSetDefault($list,$attr){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=> $v){
            $list[$k] = self::renameImageUrlOrSetDefault($v,$attr);
        }
        return $list;
    }


    /**
     * 图片增加域名，如果不存在则设置默认值
     * @param $obj
     * @param $attr
     * @return mixed
     */
    public static function renameImageUrlOrSetDefault($obj,$attr){
        $obj = self::renameImageUrl($obj,$attr);
        $obj = self::setDefaultIfNotExist($obj,$attr);
        return $obj;
    }

    /**
     * 批量定义展示状态
     * @param $skuList
     * @return array
     */
    public static function batchDefineStatusDisplayVO($skuList){
        if (empty($skuList)){
            return [];
        }
        foreach ($skuList as $k=>$v){
            $skuList[$k] = self::defineStatusDisplayVO($v);
        }
        return $skuList;
    }

    /**定义展示状态
     * @param $sku
     * @return mixed
     */
    private static function defineStatusDisplayVO($sku){
        $nowTime = time();
        $onlineTime = strtotime($sku['online_time']);
        $offlineTime = strtotime($sku['offline_time']);
        $sku['stock'] = $sku['schedule_stock']-$sku['schedule_sold'];
        if ($nowTime<$onlineTime){
            $sku['display_status'] = GoodsConstantEnum::DISPLAY_STATUS_WAITING;
            $sku['display_status_text'] =  GoodsConstantEnum::$displayStatusTextArr[GoodsConstantEnum::DISPLAY_STATUS_WAITING];
            $sku['display_status_text2'] = DateTimeUtils::formatMonthAndDayAndHourAndMinuteAndSecondChinese($onlineTime,false).'开售';
        }
        else if ($nowTime>$offlineTime){
            $sku['display_status'] = GoodsConstantEnum::DISPLAY_STATUS_END;
            $sku['display_status_text'] = GoodsConstantEnum::$displayStatusTextArr[GoodsConstantEnum::DISPLAY_STATUS_END];
            $sku['display_status_text2'] = GoodsConstantEnum::$displayStatusTextArr[GoodsConstantEnum::DISPLAY_STATUS_END];
        }
        else if ($sku['stock']<1){
            $sku['display_status'] = GoodsConstantEnum::DISPLAY_STATUS_SALE_OUT;
            $sku['display_status_text'] = GoodsConstantEnum::$displayStatusTextArr[GoodsConstantEnum::DISPLAY_STATUS_SALE_OUT];
            $sku['display_status_text2'] = GoodsConstantEnum::$displayStatusTextArr[GoodsConstantEnum::DISPLAY_STATUS_SALE_OUT];
        }
        else{
            $sku['display_status'] = GoodsConstantEnum::DISPLAY_STATUS_IN_SALE;
            $sku['display_status_text'] = GoodsConstantEnum::$displayStatusTextArr[GoodsConstantEnum::DISPLAY_STATUS_IN_SALE];
            $sku['display_status_text2'] = GoodsConstantEnum::$displayStatusTextArr[GoodsConstantEnum::DISPLAY_STATUS_IN_SALE];
        }
        return $sku;
    }

    /**
     * 批量替换
     * @param $list
     * @param $distAttr
     * @param $replaceAttr
     * @return array
     */
    public static function batchReplaceIfNotExist($list,$distAttr,$replaceAttr){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::replaceIfNotExist($v,$distAttr,$replaceAttr);
        }
        return $list;
    }
    /**
     * 替换
     * @param $obj
     * @param $distAttr
     * @param $replaceAttr
     * @return array
     */
    public static function replaceIfNotExist($obj,$distAttr,$replaceAttr){
        $dist = ArrayUtils::getArrayValue($distAttr,$obj,"");
        $rep = ArrayUtils::getArrayValue($replaceAttr,$obj,"");
        if (StringUtils::isBlank($dist)){
            $obj[$distAttr] = $rep;
        }
        return $obj;
    }

    /**
     * 批量设置默认图片
     * @param $list
     * @param $attr
     * @return array
     */
    public static function batchSetDefaultIfNotExist($list,$attr){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::setDefaultIfNotExist($v,$attr);
        }
        return $list;
    }

    /**
     * 设置默认图片
     * @param $obj
     * @param $attr
     * @return mixed
     */
    public static function setDefaultIfNotExist($obj,$attr){
        if (StringUtils::isBlank(ArrayUtils::getArrayValue($attr,$obj,''))){
            $obj[$attr] = Common::getDefaultImageUrl();
        }
        return $obj;
    }

    /**
     * 批量设置到达时间文本
     * @param $list
     * @return array
     */
    public static function batchDefineExpectArriveTimeText($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::defineExpectArriveTimeText($v);
        }
        return $list;
    }

    /**
     * 设置到达时间文本
     * @param $model
     * @return mixed
     */
    private static function defineExpectArriveTimeText($model){
        if (key_exists('expect_arrive_time',$model)){
            $expectArriveTime = $model['expect_arrive_time'];
            $model['expect_arrive_time_text'] = DateTimeUtils::formatYearAndMonthAndDaySlash($expectArriveTime);
        }
        return $model;
    }




    public static function assembleAllianceGoods($skuList){
        $skuList = self::assembleImage($skuList);
        self::batchSetAllianceGoodsStatus($skuList);
        self::batchSetStock($skuList);
        return $skuList;
    }

    /**
     * 批量设置异业联盟商品库存
     * @param $skuList
     */
    public static function batchSetStock(&$skuList){
        if (empty($skuList)){
            return;
        }
        foreach ($skuList as $k=>$v){
            $v = self::setStock($v);
            $skuList[$k] = $v;
        }
    }

    /**
     * 设置异业联盟商品的库存
     * @param $sku
     * @return mixed
     */
    public static function setStock($sku){
        if (empty($sku)){
            return $sku;
        }
        $sku['stock'] = $sku['sku_stock']-$sku['sku_sold'];
        return $sku;
    }

    /**
     * 批量设置异业联盟商品的状态
     * @param $skuList
     */
    public static function batchSetAllianceGoodsStatus(&$skuList){
        if (empty($skuList)){
            return;
        }
        foreach ($skuList as $k=>$v){
            $v = self::setAllianceGoodsStatus($v);
            $skuList[$k] = $v;
        }
    }

    /**
     * 设置异业联盟商品的状态
     * @param $sku
     * @return mixed
     */
    public static function setAllianceGoodsStatus($sku){
        if (empty($sku)){
            return $sku;
        }
        if (ArrayUtils::getArrayValue('goods_status',$sku)==GoodsConstantEnum::STATUS_UP
            &&ArrayUtils::getArrayValue('sku_status',$sku)==GoodsConstantEnum::STATUS_UP){
            $sku['status'] = GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_UP;
        }
        else{
            $sku['status'] = GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_DOWN;
        }
        $sku['status_text'] = ArrayUtils::getArrayValue( $sku['status'],GoodsConstantEnum::$allianceDisplayGoodsStatusTextArr);
        return $sku;
    }


    /**
     * [][B][C]...[D].image
     * @param $list
     * @param $imageAttrName
     * @param mixed ...$subAttrs
     * @return array
     */
    public static function batchRenameSubAttrImage($list, $imageAttrName, ...$subAttrs){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::renameSubAttrImage($v,$imageAttrName,...$subAttrs);
            $list[$k] = $v;
        }
        return $list;
    }

    /**
     * A[B][C]...[D].image
     * @param $entity
     * @param $imageAttrName
     * @param mixed ...$subAttrs
     * @return mixed
     */
    public static function renameSubAttrImage($entity, $imageAttrName, ...$subAttrs){
        $subEntity = &$entity;
        foreach ($subAttrs as $v){
            if (!key_exists($v,$subEntity)){
                return $entity;
            }
            $subEntity = &$subEntity[$v];
        }
        if (key_exists($imageAttrName,$subEntity)){
            $subEntity = GoodsDisplayDomainService::renameImageUrl($subEntity,$imageAttrName);
        }
        return $entity;
    }
}