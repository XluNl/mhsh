<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class LayDateAsset  extends AssetBundle
{
    // public $sourcePath = '@vendor/bower/layDate/laydate/';
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       'css/laydate.css'
    ];
    public $js = [
        'js/laydate.js'
    ];
    public $depends = [
    	'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset'
    ];
}
