<?php

namespace alliance\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class LayerAsset  extends AssetBundle
{
    public $sourcePath = '@bower/layer/layer';
    public $css = [
        'skin/layer.css'
    ];
    public $js = [
        'layer.js',
    ];
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $depends = [
        //'alliance\assets\AppAsset',
    ];
}
