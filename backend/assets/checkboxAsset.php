<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class checkboxAsset  extends AssetBundle
{
    public $sourcePath = '@vendor/bower/checkbox';
    public $css = [
        'checkbix.min.css'
    ];
    public $js = [
        'checkbix.min.js'
    ];
    public $depends = [
    ];
}

