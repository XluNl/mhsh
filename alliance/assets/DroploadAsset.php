<?php

namespace alliance\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class DroploadAsset  extends AssetBundle
{
    public $sourcePath = '@bower/dropload-gh-pages/dist';
    public $css = [
        'dropload.css'
    ];
    public $js = [
        'dropload.min.js',
    ];
    public $jsOptions = [
        
    ];
    public $depends = [
        'alliance\assets\AppAsset',
    ];
}
