<?php

namespace business\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class JqueryQrcodeAsset  extends AssetBundle
{
    public $sourcePath = '@bower/jquery-qrcode/src';
    public $css = [
    ];
    public $js = [
        'jquery.qrcode.js',
        'qrcode.js',
    ];
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $depends = [
        'business\assets\AppAsset',
    ];
}
