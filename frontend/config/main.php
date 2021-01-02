<?php
$params = array_merge(
	require (__DIR__ . '/../../common/config/params.php'),
	require (__DIR__ . '/../../common/config/params-local.php'),
	require (__DIR__ . '/params.php'),
	require (__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-frontend',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'frontend\controllers',
	'components' => [
		'user' => [
			'identityClass' => 'common\models\User',
			'enableAutoLogin' => true,
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning','info'],
                    'categories' => ['application']
				],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['pay'],
                    'levels' => ['error', 'warning','info'],
                    'logVars' => ['*'],
                    'logFile' => '@runtime/logs/pay.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['api'],
                    'levels' => ['info'],
                    'logVars' => ['*'],
                    'logFile' => '@runtime/logs/api.log',
                ]
			],
		],
	    'urlManager' => [
	        'enablePrettyUrl' => true,
	        'showScriptName' => false,
	        'rules' => [],
	    ],
		'errorHandler' => [
			'errorAction' => 'error/error',
		],
        'frontendWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'frontendWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'frontendWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_frontendWechatReturnUrl', // returnUrl 存储在会话中
        ],
        'officialWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'officialWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'officialWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_officialWechatReturnUrl', // returnUrl 存储在会话中
        ],
        'response' => [
            'on afterSend' => ['\common\components\GlobalApiLog','apiLog']
        ],
        'keRuYun' => ['class'=>'\common\components\keruyun\KryService']
	],
	'modules' => [
		'customer' => [
			'class' => 'frontend\modules\customer\Customer',
		],

	],
	'params' => $params,
];
