<?php


namespace common\services;


use common\models\Banner;
use common\models\CommonStatus;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use common\models\GoodsSchedule;
use frontend\services\GoodsDisplayDomainService;
use yii\db\Query;

class BannerService
{

    /**
     * 有效banner
     * @param $id
     * @param null $companyId
     * @param null $bannerType
     * @return array|mixed
     */
    public static function getValidBanner($id,$companyId=null, $bannerType=null){
        $bannerList = self::getValidBannerList($id,$companyId,$bannerType);
        if (empty($bannerList)){
            return [];
        }
        return $bannerList[0];
    }


    /**
     * 有效banner
     * @param $ids
     * @param $companyId
     * @param $bannerType
     * @return array
     */
    public static function getValidBannerList($ids=null, $companyId=null, $bannerType=null){
        $conditions = ['status'=>CommonStatus::STATUS_ACTIVE];
        if (StringUtils::isNotEmpty($ids)){
            $conditions['id'] = $ids;
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if (StringUtils::isNotBlank($bannerType)){
            $conditions['type'] = $bannerType;
        }
        $nowTimeStr = DateTimeUtils::parseStandardWLongDate();
        $list =  (new Query())->from(Banner::tableName())->where(
        [
            'and',
            $conditions,
            ['<=','online_time',$nowTimeStr],
            ['>=','offline_time',$nowTimeStr]
        ])->all();
        $list = GoodsDisplayDomainService::batchRenameImageUrl($list,'images');
        return $list;
    }

    /**
     * 获取model
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|Banner|\yii\db\ActiveRecord|null
     */
    public static function getModel($id, $company_id=null, $model = false){
        $conditions = ['id' => $id];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Banner::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Banner::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 获取
     * @param $company_id
     * @param $type
     * @return array
     */
    public static function getActiveModelList($company_id,$type=null){
        $conditions = ['company_id' => $company_id,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($type)){
            $conditions['type'] = $type;
        }
        return (new Query())->from(Banner::tableName())->where($conditions)->all();
    }

    /**
     * 获取banner关联信息
     * @param Banner $banner
     * @return Banner
     */
    public static function restoreLinkInfo(Banner $banner)
    {
        if ($banner['sub_type'] == Banner::SUB_TYPE_SCHEDULE_DETAIL) {
            $banner['link_info_restore'] = GoodsSchedule::find()->where(['id' => $banner['link_info']])->with(['goods', 'goodsSku', 'goodsDetail'])->asArray()->one();
            $banner['link_info_restore'] = GoodsDisplayDomainService::renameSubAttrImage($banner['link_info_restore'],'goods_img','goods');
            $banner['link_info_restore'] = GoodsDisplayDomainService::renameSubAttrImage($banner['link_info_restore'],'sku_img','goodsSku');

        }
        if ($banner['sub_type'] == Banner::SUB_TYPE_SCHEDULE_LIST) {
            $banner['link_info_restore'] = GoodsSchedule::find()->where(['id' => json_decode($banner['link_info'],true)])->with(['goods', 'goodsSku'])->asArray()->all();
            $banner['link_info_restore'] = GoodsDisplayDomainService::batchRenameSubAttrImage($banner['link_info_restore'],'goods_img','goods');
            $banner['link_info_restore'] = GoodsDisplayDomainService::batchRenameSubAttrImage($banner['link_info_restore'],'sku_img','goodsSku');
        }
        return $banner;
    }
}