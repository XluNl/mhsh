<?php
$params = array_merge(
	require (__DIR__ . '/../../common/config/params.php'),
	require (__DIR__ . '/../../common/config/params-local.php'),
	require (__DIR__ . '/params.php'),
	require (__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-api',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'api\controllers',
	'bootstrap' => ['log'],
	'components' => [
		'user' => [
			'identityClass' => 'common\models\User',
			'enableAutoLogin' => true,
		],
		'urlManager' => [
			'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
		],
		'session' => [
			//'class' => 'yii\web\DbSession',
			//'db' => 'db', // 数据库连接的应用组件ID，默认为'db'.
			//'sessionTable' => 'sptx_weixin_session', // session 数据表名，默认为'session'.
			'timeout'=>7200,
		],
	   
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		/*'response' => [
            'class' => \yii\web\Response::className(),
			'format'=>'json',
        ],*/
	],
	'params' => $params,
];
