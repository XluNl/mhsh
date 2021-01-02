<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;
/**
 * Main backend application asset bundle.
 */
class BootstrapTreeviewAsset  extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-treeview/dist';
    public $css = [
        'bootstrap-treeview.css',
    ];
    public $js = [
        'bootstrap-treeview-my.js',
        
    ];
    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];
    public $cssOptions = [
        'position'=>View::POS_HEAD
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapThemeAsset',
    ];
}
