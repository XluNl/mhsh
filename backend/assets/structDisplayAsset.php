<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class structDisplayAsset  extends AssetBundle
{
    public $sourcePath = '@vendor/bower/struct-display';
    public $css = [
        'jquery.jOrgChart.css',
        "custom.css"
    ];
    public $js = [
        'js/jquery.jOrgChart.js'
    ];
    public $depends = [
    ];
}

