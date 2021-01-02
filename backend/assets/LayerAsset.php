<?php

namespace backend\assets;

use yii\web\AssetBundle;
/**
 * Main backend application asset bundle.
 */
class LayerAsset  extends AssetBundle
{
    public $sourcePath = '@vendor/bower/layer/layer/';
    public $css = [
       // 'theme/default/laydate.css'
    ];
    public $js = [
        'layer.js'
    ];
    public $depends = [
    ];
}
