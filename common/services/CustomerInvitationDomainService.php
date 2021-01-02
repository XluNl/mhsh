<?php


namespace common\services;


use common\models\Common;
use common\models\CustomerInvitationLevel;
use common\utils\ArrayUtils;
use common\utils\PhoneUtils;

class CustomerInvitationDomainService
{

    public static function batchSetInvitationLevelText(&$list){
        if (empty($list)){
            return;
        }
        foreach ($list as $k=>$v){
            self::setInvitationLevelText($v);
            $list[$k] = $v;
        }
    }

    public static function setInvitationLevelText(&$arr){
        if (empty($arr)){
            return;
        }
        if (!key_exists('level',$arr)||$arr['level']==null){
            $arr['level'] = CustomerInvitationLevel::LEVEL_NORMAL;
        }
        $arr['level_text']=ArrayUtils::getArrayValue($arr['level'],CustomerInvitationLevel::$levelTextArr);
    }

    public static function batchSetPhoneMark(&$list){
        if (empty($list)){
            return;
        }
        foreach ($list as $k=>$v){
            self::setPhoneMarkText($v);
            $list[$k] = $v;
        }
    }

    public static function setPhoneMarkText(&$arr){
        if (empty($arr)){
            return;
        }
        $arr['phone_text']=PhoneUtils::dataDesensitization($arr['phone'],3, 4);
    }
}