<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;
/**
 * Main backend application asset bundle.
 */
class JqueryLabelautyAsset  extends AssetBundle
{
    public $sourcePath = '@bower/jquery-labelauty/source';
    public $css = [
        'jquery-labelauty.css',
    ];
    public $js = [
        'jquery-labelauty.js',
    ];
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
