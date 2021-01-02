<?php


namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\Customer;
use common\utils\PhoneUtils;

class CustomerService extends \common\services\CustomerService
{

    /**
     * 查找用户
     * @param $keyword
     * @return array
     */
    public static function searchCustomerUserOption($keyword){
        $customers = self::searchCustomerUserP($keyword);
        $res = [];
        if (!empty($customers)){
            foreach ($customers as $customer){
                $item= [
                    'customer_id'=>$customer['id'],
                    'phone'=>$customer['phone'],
                    'name'=>$customer['nickname'],
                    'head_img_url'=>'',
                ];
                if (key_exists('user',$customer)){
                    $item['head_img_url'] = $customer['user']['head_img_url'];
                }
                $res[] = $item;
            }
            $res = GoodsDisplayDomainService::batchRenameImageUrl($res,'head_img_url');
            PhoneUtils::batchReplacePhoneMark($res,'phone');
        }
        return $res;
    }
}