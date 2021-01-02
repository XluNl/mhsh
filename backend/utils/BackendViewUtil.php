<?php


namespace backend\utils;


use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\helpers\Html;
use yii\helpers\Url;

class BackendViewUtil
{
    /**
     * @param string|integer $key
     * @param array $statusArr
     * @param array $statusCssArr
     * @return string
     */
    public static function getArrayWithLabel($key,array $statusArr,array $statusCssArr){
        $statusText = ArrayUtils::getArrayValue($key,$statusArr);
        $statusLabel = ArrayUtils::getArrayValue($key,$statusCssArr,'label label-info');
        return Html::tag("label",$statusText,['class'=>$statusLabel]);
    }

    /**
     * 生成跳转Button
     * @param $buttonName
     * @param $jumpUrl
     * @param null $className
     * @param null $spanIcon
     * @return string
     */
    public static function generateOperationButton($buttonName,$jumpUrl,$className=null,$spanIcon=null){
        $options = [
            'onclick' => 'location="'.Url::toRoute($jumpUrl).'"',
        ];
        if (!StringUtils::isBlank($className)){
            $options['class'] = $className;
        }
        $content = $buttonName;
        if (!StringUtils::isBlank($spanIcon)){
            $content = Html::tag("span",'',['class'=>$spanIcon]).$content;
        }
        return Html::button($content, $options);
    }

    /**
     * 生成跳转ATag
     * @param $aTagName
     * @param $jumpUrl
     * @param null $className
     * @param null $spanIcon
     * @param null $confirmMsg
     * @param array $options
     * @return string
     */
    public static function generateOperationATag($aTagName,$jumpUrl,$className=null,$spanIcon=null,$confirmMsg=null,$options=[]){
        if (!StringUtils::isBlank($className)){
            $options['class'] = $className;
        }
        if (!StringUtils::isBlank($confirmMsg)){
            $options['data-confirm'] = $confirmMsg;
        }
        $content = $aTagName;
        if (!StringUtils::isBlank($spanIcon)){
            $content = Html::tag("span",'',['class'=>$spanIcon]).$content;
        }
        return Html::a($content,$jumpUrl, $options);
    }



}