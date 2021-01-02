<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class loadingAsset  extends AssetBundle
{
    public $sourcePath = '@vendor/bower/loading';
    public $css = [
        'dist/showLoading.css'
    ];
    public $js = [
        'dist/jquery.showLoading.min.js'
    ];
    public $depends = [
    ];
}
