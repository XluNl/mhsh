<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ICheckAsset  extends AssetBundle
{
    public $sourcePath = '@bower/icheck';
    public $css = [
        'skins/all.css'
    ];
    public $js = [
        'icheck.min.js',
    ];
    public $jsOptions = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\web\JqueryAsset'
    ];
 
    
}
