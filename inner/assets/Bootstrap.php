<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace inner\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Bootstrap extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
	public $css = [
		'css/basic/bootstrap/bootstrap.min.css',
		'css/basic/style.css',
		'css/basic/style-responsive.css',
		'css/font-awesome/css/font-awesome.min.css',
	];
	public $js = [
		'js/jquery.min.js',
		'js/bootstrap.min.js',
		'js/apps.js',
		'js/demo-panel.js',
		'js/all.js',
	];
	public $depends = [
		//'yii\web\YiiAsset',
		//'yii\bootstrap\BootstrapAsset',
	];
}
