<?php

namespace business\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class WeuiJSAsset  extends AssetBundle
{
    public $sourcePath = '@bower/weui.js/dist';
    public $css = [
        'weui.min.css'
    ];
    public $js = [
        'weui.min.js',
    ];
    public $jsOptions = [
        
    ];
    public $depends = [
        //'business\assets\WeuiAsset',
    ];
}
