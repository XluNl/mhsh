<?php

namespace backend\assets;

use yii\web\AssetBundle;
use Yii;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class BootboxAsset  extends AssetBundle
{
    public $sourcePath = '@bower/bootbox';
    public $css = [
    ];
    public $js = [
        'bootbox.js',
    ];
    public $jsOptions = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public static function overrideSystemConfirm(){
    $js = <<<JS
         yii.confirm = function(message, ok, cancel) {
                bootbox.setLocale("zh_CN");
                bootbox.confirm(
                {
                    message: message,
                    callback: function (confirmed) {
                        if (confirmed) {
                          !ok || ok();
                        }
                        else {
                          !cancel || cancel();
                        }
                    }
                });
            }
JS;
            Yii::$app->view->registerJs($js);
    }
    
}
