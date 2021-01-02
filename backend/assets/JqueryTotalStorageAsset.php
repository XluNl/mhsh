<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;
/**
 * Main backend application asset bundle.
 */
class JqueryTotalStorageAsset  extends AssetBundle
{
    public $sourcePath = '@bower/jquery-total-storage';
    public $css = [
    ];
    public $js = [
        'jquery.total-storage.min.js',
    ];
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
