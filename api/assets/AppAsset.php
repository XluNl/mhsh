<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace api\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [
		'css/bootstrap/bootstrap.min.css',
		'css/style.css',
		'css/style-responsive.css',
		'css/font-awesome/css/font-awesome.min.css',
	];
	public $js = [
		'js/jquery.min.js',
		'js/bootstrap.min.js',
		'js/apps.js',
		'js/demo-panel.js',

	];
	public $depends = [
		//'yii\web\YiiAsset',
		//'yii\bootstrap\BootstrapAsset',
	];
}