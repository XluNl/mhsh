<?php


namespace frontend\services;

use common\models\Banner;
use common\models\Common;
use common\models\GoodsSchedule;
use common\utils\StringUtils;
use yii\helpers\Json;

class BannerService extends \common\services\BannerService
{


    public static function getBanner($bannerId,$companyId){

    }



    /**
     * 过滤有效banner 批量
     * @param $bannerList
     * @param $deliveryId
     * @return array
     */
	public static function filterValidBannerList($bannerList,$deliveryId){
        if (empty($bannerList)){
            return [];
        }
        $validList = [];
        foreach ($bannerList as $v){
            $res = self::filterValidBanner($v,$deliveryId);
            if (!empty($res)){
                $validList[] = $res;
            }
        }
        return $validList;
    }

    /**
     * 过滤有效banner 单个
     * @param $banner
     * @param $deliveryId
     * @return |null
     */
    public static function filterValidBanner($banner,$deliveryId){
	    switch ($banner['sub_type']){
            case Banner::SUB_TYPE_URL_JUMP:
            case Banner::SUB_TYPE_DEFAULT:
                return $banner;
            case Banner::SUB_TYPE_SCHEDULE_DETAIL:
                if (StringUtils::isBlank($banner['link_info'])){return null;}
                $scheduleModels = GoodsScheduleService::getDisplayUpByIds(null,$banner['company_id'],$banner['link_info'],null,$deliveryId);
                if (empty($scheduleModels)){return null;}
                $scheduleModels = GoodsDisplayDomainService::assembleStatusAndImageAndExceptTime($scheduleModels);
                $banner['goodsInfo'] = $scheduleModels[0];
                return $banner;
            case Banner::SUB_TYPE_SCHEDULE_LIST:
                if (StringUtils::isBlank($banner['link_info'])){return null;}
                $banner['link_info'] = Json::decode($banner['link_info']);
                if (StringUtils::isEmpty($banner['link_info'])){return null;}
                $scheduleModels = GoodsScheduleService::getDisplayUpByIds(null,$banner['company_id'],$banner['link_info'],null,$deliveryId);
                if (empty($scheduleModels)){return null;}
                $scheduleModels = GoodsDisplayDomainService::assembleStatusAndImageAndExceptTime($scheduleModels);
                $banner['goodsInfoList'] = $scheduleModels;
                return $banner;
            default:
                return null;
        }
    }


	public static function displayBannerData($banner_list){
		foreach ($banner_list as $index => &$banner) {
			if($banner['sub_type']==Banner::SUB_TYPE_SCHEDULE_DETAIL && !empty($banner['link_info'])){
				$goodsInfo  = GoodsSchedule::find()->where(['id'=>$banner['link_info']])->with(['goods'])->asArray()->one();
				$banner['goodsInfo']['goods_id'] = $goodsInfo['goods_id'];
				$banner['goodsInfo']['schedule_id'] = $goodsInfo['id'];
				$banner['goodsInfo']['schedule_display_channel'] = $goodsInfo['schedule_display_channel']; 
			}
			// if($banner['sub_type']==Banner::TYPE_SCHEDULE_LIST && !empty($banner['link_info'])){
			// 	$schedule_ids = json_decode($banner['link_info'],true);
			// 	foreach ($schedule_ids as $key => $schedule_id) {
			// 		$goodsInfo  = GoodsSchedule::find()->where(['id'=>$schedule_id])->with(['goods'])->asArray()->one();
			// 		$banner['goodsInfo'][$key]['goods_id'] = $goodsInfo['goods_id'];
			// 		$banner['goodsInfo'][$key]['schedule_id'] = $goodsInfo['id'];
			// 		$banner['goodsInfo'][$key]['schedule_display_channel'] = $goodsInfo['schedule_display_channel']; 
			// 	}
			// }
			$banner['images'] = Common::generateAbsoluteUrl($banner['images']);
		}
		return $banner_list;
	}

	public static function checkLinkScheduleExpTime($banner_list){
		if(empty($banner_list)){
			return $banner_list;
		}
		// foreach ($banner_list as $key => &$banner) {
		// 	if($banner['sub_type'] == Banner::TYPE_SCHEDULE_DATAIL){
		// 		$sch = GoodsSchedule::find()->where([])->one();
		// 		if(!$sch){
		// 			unset($banner_list[$key]);
		// 		}
		// 	}
		// 	if($banner['sub_type'] == Banner::TYPE_SCHEDULE_LIST){
		// 		$banner->restoreForm();
		// 		$sch = GoodsSchedule::find()->where([])->all();
		// 		unset($banner_list[$key]);
		// 	}
		// }
	}

}