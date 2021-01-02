<?php


namespace common\services;


use common\models\Common;
use common\models\SystemOptions;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;
use Yii;

class SystemOptionsService
{

    public static function getSystemOptionValue($optionField, $defaultValue=null){
        $initCompanyId = Yii::$app->params['option.init.companyId'];
        $option = self::getValue($optionField,SystemOptions::BIZ_TYPE_SYSTEM,$initCompanyId,$initCompanyId);
        if (empty($option)){
            return $defaultValue===null?ArrayUtils::getArrayValue($optionField,SystemOptions::$optionValueArr,null):$defaultValue;
        }
        return trim($option['option_value']);
    }


    public static function getSystemOptionsList(){
        $initCompanyId = Yii::$app->params['option.init.companyId'];
        $existOptions = self::getValues(SystemOptions::$optionSystemArr,SystemOptions::BIZ_TYPE_SYSTEM,$initCompanyId,$initCompanyId,true);
        $options = [];
        foreach (SystemOptions::$optionSystemArr as $v){
            $existOne = self::getItemByOptionField($v,$existOptions);
            if ($existOne!==null){
                $options[] = $existOne;
            }
            else{
                $systemOption = new SystemOptions();
                $systemOption->option_field = $v;
                $systemOption->option_name = ArrayUtils::getArrayValue($v,SystemOptions::$optionFieldArr);
                $systemOption->option_value = ArrayUtils::getArrayValue($v,SystemOptions::$optionValueArr);
                $systemOption->biz_id = $initCompanyId;
                $systemOption->biz_type = SystemOptions::BIZ_TYPE_SYSTEM;
                $systemOption->company_id = $initCompanyId;
                $options[] = $systemOption;
            }
        }
        return $options;
    }


    public static function setSystemOptionValue($optionField,$optionValue){
        $initCompanyId = Yii::$app->params['option.init.companyId'];
        $option = self::getValue($optionField,SystemOptions::BIZ_TYPE_SYSTEM,$initCompanyId,$initCompanyId,true);
        if (empty($option)){
            $option = new SystemOptions();
            $option->option_field = $optionField;
            $option->option_name = ArrayUtils::getArrayValue($optionField,SystemOptions::$optionFieldArr);
            $option->option_value = $optionValue;
            $option->biz_id = $initCompanyId;
            $option->biz_type = SystemOptions::BIZ_TYPE_SYSTEM ;
            $option->company_id = $initCompanyId;
        }
        else{
            $option->option_value = $optionValue;
        }
        if (!$option->save()){
            return [false,'系统配置保存失败'];
        }
        return [true,''];
    }


    private static function getValue($optionField,$bizType,$bizId,$companyId,$model=false){
        $conditions=['option_field'=>$optionField,'biz_type'=>$bizType,'biz_id'=>$bizId];
        if (!StringUtils::isBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return SystemOptions::findOne($conditions);
        }
        else{
            $option = (new Query())->from(SystemOptions::tableName())->where($conditions)->one();
            return $option===false?null:$option;
        }
    }

    private static function getValues($optionFields,$bizType,$bizId,$companyId,$model=false){
        $conditions=['option_field'=>$optionFields,'biz_type'=>$bizType,'biz_id'=>$bizId];
        if (!StringUtils::isBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return SystemOptions::findAll($conditions);
        }
        else{
            $option = (new Query())->from(SystemOptions::tableName())->where($conditions)->all();
            return $option;
        }
    }


    private static function getItemByOptionField($optionField,$systemOptionsList){
        if (empty($systemOptionsList)){
            return null;
        }
        foreach ($systemOptionsList as $systemOptions){
            if ($systemOptions['option_field']==$optionField){
                return $systemOptions;
            }
        }
        return null;
    }


}