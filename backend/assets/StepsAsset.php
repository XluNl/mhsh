<?php


namespace backend\assets;


use yii\web\AssetBundle;

/**
 * 步骤流展示插件
 * Class StepsAsset
 * @package backend\assets
 */
class StepsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'plugins/steps/steps.min.css'
    ];
    public $js = [
        'plugins/steps/steps.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\web\JqueryAsset',
    ];
}