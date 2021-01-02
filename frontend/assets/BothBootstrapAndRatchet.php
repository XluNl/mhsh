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
class BothBootstrapAndRatchet extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
	public $css = [
		'css/basic/bootstrap/bootstrap.min.css',
		'css/basic/style.css',
		'css/basic/style-responsive.css',
		'css/ratchet.css',
		'css/style.css',
	    'css/font-awesome.css'
	];
	public $js = [
		'js/jquery.min.js',
		'js/bootstrap.min.js',
		'js/apps.js',
		'js/demo-panel.js',
		'js/ratchet.js',
		'js/push.js',
		'js/all.js',
	];
	public $depends = [
		//'yii\web\YiiAsset',
		//'yii\bootstrap\BootstrapAsset',
	];
}
