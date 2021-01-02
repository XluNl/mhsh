<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class jqPrint  extends AssetBundle
{
    public $sourcePath = '@vendor/bower/jqprint';
    public $css = [
           ];
    public $js = [
        'jqprint.min.js',
    ];
    public $depends = [
    ];
}
