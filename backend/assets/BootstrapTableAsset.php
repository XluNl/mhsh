<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class BootstrapTableAsset  extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-table/dist';
    public $css = [
        'bootstrap-table.min.css',
    ];
    public $js = [
        'bootstrap-table.min.js',
        'locale/bootstrap-table-zh-CN.min.js',
        'extensions/export/bootstrap-table-export.min.js',
        //'extensions/export/FileSaver.min.js',
        //'extensions/export/jspdf.min.js',
        //'extensions/export/jspdf.plugin.autotable.js',
        'extensions/export/tableExport.js',
        
    ];
    public $jsOptions = [
    ];
    public $cssOptions = [
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapThemeAsset',
    ];
}
