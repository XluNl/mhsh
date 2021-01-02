<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class DavidshimjsQrcodejsAsset  extends AssetBundle
{
    public $sourcePath = '@bower/davidshimjs-qrcodejs';
    public $css = [
    ];
    public $js = [
        'qrcode.min.js',
    ];
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $depends = [
        'frontend\assets\AppAsset',
    ];
}
