<?php

namespace backend\assets;
use yii\web\View;
use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $css = [
        'css/site.css',
        'css/style.css',
        //'css/main.css'
        //'css/style-responsive.css',
    ];
    public $js = [
        //'js/main.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
