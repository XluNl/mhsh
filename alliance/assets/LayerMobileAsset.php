<?php

namespace alliance\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class LayerMobileAsset  extends AssetBundle
{
    public $sourcePath = '@bower/layer.mobile/layer_mobile';
    public $css = [
        'need/layer.css'
    ];
    public $js = [
        'layer.js',
    ];
    public $jsOptions = [
        
    ];
    public $depends = [
        'alliance\assets\AppAsset',
    ];
}
