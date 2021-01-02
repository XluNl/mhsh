<?php


namespace backend\models;


use yii\helpers\ArrayHelper;

class ModelViewUtils
{
    /**
     *
     *
     *
     *  img($src, $options = [])
        label($content, $for = null, $options = [])
        a($text, $url = null, $options = [])
        hiddenInput($name, $value = null, $options = [])
        textInput($name, $value = null, $options = [])
        textarea($name, $value = '', $options = [])
        //radio($name, $checked = false, $options = [])
        //checkbox($name, $checked = false, $options = [])
        dropDownList($name, $selection = null, $items = [], $options = [])
        listBox($name, $selection = null, $items = [], $options = [])
        checkboxList($name, $selection = null, $items = [], $options = [])
        radioList($name, $selection = null, $items = [], $options = [])
     *
     *
     * @param array $options
     * @return array
     */
    public static function mergeDefaultOptions($options=[]){
        return ArrayHelper::merge($options,[
            'class'=>'form-control'
        ]);



    }

    public static function getAttrId($modelId,$key){
        return "{$modelId}_{$key}";
    }

    public static function setValueHtml($type,$modelId,$key){
        $str = "let value{$key}= $(this).attr(\"data-{$key}\");".PHP_EOL;
        $attrObj = "$('#".self::getAttrId($modelId,$key)."')";
        if (in_array($type,[
            'hiddenInput','textInput','textarea','dropDownList',
        ])){
            $str .= "{$attrObj}.val(value{$key});".PHP_EOL;
        }
        else if (in_array($type,[
            'label','span'
        ])){
            $str .= "{$attrObj}.html(value{$key});".PHP_EOL;
        }
        else if (in_array($type,[
            'listBox','checkboxList','radioList',
        ])){

        }
        return $str;
    }


    public static function isHiddenRow($type){
        if (in_array($type,[
            'hiddenInput'
        ])){
            return true;
        }
        else{
            return false;
        }
    }
}