<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;
/**
 * Main backend application asset bundle.
 */
class EchartsAsset  extends AssetBundle
{
    public $sourcePath = '@bower/echarts/dist';
    public $css = [
    ];
    public $js = [
        'echarts.min.js',
    ];
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $depends = [
        
    ];
}
