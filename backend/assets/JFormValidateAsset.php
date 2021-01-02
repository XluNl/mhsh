<?php

namespace backend\assets;

use yii\web\AssetBundle;

class JFormValidateAsset  extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       // 'theme/default/laydate.css'
    ];
    public $js = [
        'js/jquery.form.min.js',
        'js/jquery.validate.min.js',
    ];
    public $depends = [
        // 'yii\web\JqueryAsset'
    ];
}
