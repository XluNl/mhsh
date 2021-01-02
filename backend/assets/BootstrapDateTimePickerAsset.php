<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class BootstrapDateTimePickerAsset  extends AssetBundle
{
    public $sourcePath = '@vendor/bower/smalot-bootstrap-datetimepicker';
    public $css = [
        'css/bootstrap-datetimepicker.min.css'
    ];
    public $js = [
        'js/bootstrap-datetimepicker.min.js',
        'js/locales/bootstrap-datetimepicker.zh-CN.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}
