<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NewBootstrap extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [
		'css/font-awesome.min.css',
	    'css/style.css',
	];
	public $js = [
		'js/all.js',
	];
	public $jsOptions = [
	    'position' => \yii\web\View::POS_HEAD,
	];
	public $depends = [
		'yii\web\YiiAsset',
	    'yii\web\JqueryAsset',
		'yii\bootstrap\BootstrapAsset',
	];
}
