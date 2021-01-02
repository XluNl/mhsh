<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace alliance\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
	public $css = [
		'css/common.css',
		'css/index.css',
	    'css/swiper.min.css'
	];
	public $js = [
		'js/jQuery.js',
	    'js/jquery-ui.min.js',
	    'js/swipe.js',
	    'js/swiper.min.js',
	    //'js/common.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
	];
}
