<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiichina\icheck;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for client validation.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ICheckAsset extends AssetBundle
{
    public $sourcePath = '@bower/icheck';

    public $js = [
        'icheck.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
