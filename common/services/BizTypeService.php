<?php


namespace common\services;


use common\models\BizTypeEnum;
use yii\helpers\ArrayHelper;

class BizTypeService
{
    public static function getOptionsByBizType($bizType,$companyId=null){
        if ($bizType==BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE||$bizType==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
            $customerModels = CustomerService::getAllActiveModel();
            $result = [];
            if (!empty($customerModels)){
                foreach ($customerModels as $v){
                    $result[$v['id']] = "{$v['id']}-{$v['nickname']}-{$v['phone']}";
                }
            }
            return $result;
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_POPULARIZER){
            $popularizerModels = PopularizerService::getAllActiveModel(null,$companyId);

            $result = [];
            if (!empty($popularizerModels)){
                foreach ($popularizerModels as $v){
                    $result[$v['id']] = "{$v['id']}-{$v['nickname']}-{$v['phone']}";
                }
            }
            return $result;
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_DELIVERY){
            $deliveryModels = DeliveryService::getAllActiveModel(null,$companyId);
            $result = [];
            if (!empty($deliveryModels)){
                foreach ($deliveryModels as $v){
                    $result[$v['id']] = "{$v['id']}-{$v['nickname']}-{$v['phone']}";
                }
            }
            return $result;
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_AGENT){
            $companyModels = CompanyService::getAllActiveModel();
            $result = [];
            if (!empty($companyModels)){
                foreach ($companyModels as $v){
                    $result[$v['id']] = "{$v['id']}-{$v['name']}-{$v['contact']}-{$v['telphone']}";
                }
            }
            return $result;
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_HA){
            $allianceModels = AllianceService::getAllActiveModel(null,$companyId);
            $result = [];
            if (!empty($allianceModels)){
                foreach ($allianceModels as $v){
                    $result[$v['id']] = "{$v['id']}-{$v['nickname']}-{$v['phone']}";
                }
            }
            return $result;
        }

        else{
            return [];
        }
    }

    /**
     * 补全账户名称
     * @param $bizArr
     * @param null $companyId
     * @return mixed
     */
    public static function completeByBizType($bizArr,$companyId=null){
        $customerIds = [];
        $popularizerIds = [];
        $deliveryIds = [];
        $companyIds = [];
        foreach ($bizArr as $v){
            if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE||$v['biz_type']==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
                $customerIds[] = $v['biz_id'];
            }
            else if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_POPULARIZER){
                $popularizerIds[] = $v['biz_id'];
            }
            else if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_DELIVERY){
                $deliveryIds[] = $v['biz_id'];
            }
            else if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_AGENT){
                $companyIds[] = $v['biz_id'];
            }
        }
        $customerModels = empty($customerIds)?[]:CustomerService::getAllActiveModel($customerIds);
        $customerModels = empty($customerModels)?[]:ArrayHelper::index($customerModels,'id');
        $popularizerModels = empty($popularizerIds)?[]:PopularizerService::getAllActiveModel($popularizerIds,$companyId);
        $popularizerModels = empty($popularizerModels)?[]:ArrayHelper::index($popularizerModels,'id');
        $deliveryModels = empty($deliveryIds)?[]:DeliveryService::getAllActiveModel($deliveryIds,$companyId);
        $deliveryModels = empty($deliveryModels)?[]:ArrayHelper::index($deliveryModels,'id');
        $companyModels = empty($companyId)?[]:CompanyService::getAllActiveModel($companyId);
        $companyModels = empty($companyModels)?[]:ArrayHelper::index($companyModels,'id');
        foreach ($bizArr as $k=>$v){
            if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE||$v['biz_type']==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
                if (key_exists($v['biz_id'],$customerModels)){
                    $vv = $customerModels[$v['biz_id']];
                    $v['biz_name'] = "{$vv['id']}-{$vv['nickname']}-{$vv['phone']}";
                }
                else{
                    $v['biz_name']= "";
                }
            }
            else if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_POPULARIZER){
                if (key_exists($v['biz_id'],$popularizerModels)){
                    $vv = $popularizerModels[$v['biz_id']];
                    $v['biz_name'] = "{$vv['id']}-{$vv['nickname']}-{$vv['phone']}";
                }
                else{
                    $v['biz_name']= "";
                }
            }
            else if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_DELIVERY){
                if (key_exists($v['biz_id'],$deliveryModels)){
                    $vv = $deliveryModels[$v['biz_id']];
                    $v['biz_name'] = "{$vv['id']}-{$vv['nickname']}-{$vv['phone']}";
                }
                else{
                    $v['biz_name']= "";
                }
            }
            else if ($v['biz_type']==BizTypeEnum::BIZ_TYPE_AGENT){
                if (key_exists($v['biz_id'],$companyModels)){
                    $vv = $companyModels[$v['biz_id']];
                    $v['biz_name'] = "{$vv['id']}-{$vv['name']}-{$vv['contact']}-{$vv['telphone']}";
                }
                else{
                    $v['biz_name']= "";
                }
            }
            $bizArr[$k]= $v;
        }
        return $bizArr;
    }


    public static function getByBizTypeAndId($bizType,$bizId,$companyId=null){
        if ($bizType==BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE||$bizType==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
            return  CustomerService::getActiveModel($bizId,false);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_POPULARIZER){
            return PopularizerService::getActiveModel($bizId,$companyId,false);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_DELIVERY){
            return DeliveryService::getActiveModel($bizId,$companyId,false);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_AGENT){
            return CompanyService::getActiveModel($bizId,false);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_HA){
            return AllianceService::getActiveModel($bizId,$companyId,false);
        }
        else{
            return null;
        }
    }

}