<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class JqueryDateTimePickerAsset  extends AssetBundle
{
    public $sourcePath = '@vendor/bower/jquery-datetimepicker';
    public $css = [
        'jquery.datetimepicker.min.css'
    ];
    public $js = [
        'build/jquery.datetimepicker.full.min.js'
    ];
    public $depends = [
    ];
}
