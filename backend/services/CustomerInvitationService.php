<?php


namespace backend\services;


use backend\models\searches\CustomerInvitationSearch;
use common\models\Customer;
use common\models\CustomerInvitation;
use common\services\CustomerInvitationDomainService;
use common\services\GoodsDisplayDomainService;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\data\ActiveDataProvider;

class CustomerInvitationService extends \common\services\CustomerInvitationService
{
    public static function completeCustomerInvitationData(ActiveDataProvider $dataProvider, CustomerInvitationSearch $searchModel){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $oneLevels = $dataProvider->getModels();
        /* 补全信息*/
        CustomerInvitationDomainService::batchSetInvitationLevelText($oneLevels);
        CustomerInvitationDomainService::batchSetPhoneMark($oneLevels);
        $oneLevels= GoodsDisplayDomainService::batchRenameImageUrl($oneLevels,'head_img_url');

        $oneLevelIds = ArrayUtils::getColumnWithoutNull('customer_id',$oneLevels);
        $detail = [];
        if (!empty($oneLevels)){
            $oneLevelDetail = self::getOneLevelInvitationDetail($searchModel->parent_id,$searchModel->start_time,$searchModel->end_time,$oneLevelIds);
            $twoLevels =  self::getCustomerByParentId($oneLevelIds);
            $twoLevelDetail = self::getTwoLevelInvitationDetail($searchModel->parent_id,$searchModel->start_time,$searchModel->end_time,$oneLevelIds);
            $detail = self::assembleCustomerInvitationData($oneLevels, $oneLevelDetail, $twoLevels, $twoLevelDetail, $detail);
        }
        foreach ($oneLevels as $oneLevelK=>$oneLevelV){
            foreach ($detail as $detailK=>$detailV){
                if ($oneLevelV['customer_id']==$detailV['customer_id']){
                    $oneLevelV["statistics"] = $detailV;
                    $oneLevelV["children"] = $detailV['children'];
                    $oneLevels[$oneLevelK] = $oneLevelV;
                }
            }
        }
        $dataProvider->setModels($oneLevels);
        return $dataProvider;
    }



    /**
     * 找到客户相关的绑定关系
     * @param $customerId
     * @return array|\yii\db\ActiveRecord[]
     */
    public  static function getRelativeCustomer($customerId){
        $customers = CustomerInvitation::find()->where([
            'or',
            ['customer_id'=>$customerId],
            ['parent_id'=>$customerId],
        ])->all();
        return $customers;
    }

}