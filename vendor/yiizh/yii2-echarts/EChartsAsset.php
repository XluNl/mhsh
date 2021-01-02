<?php
/**
 * @author Di Zhang <zhangdi_me@163.com>
 */

namespace yiizh\echarts;

use yii\web\AssetBundle;

class EChartsAsset extends AssetBundle
{
    public $sourcePath = '@yiizh/echarts/assets';
    public $js = [
        'echarts.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}