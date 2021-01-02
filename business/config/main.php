<?php
$params = array_merge(
	require (__DIR__ . '/../../common/config/params.php'),
	require (__DIR__ . '/../../common/config/params-local.php'),
	require (__DIR__ . '/params.php'),
	require (__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-business',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'business\controllers',
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
                    'categories' => ['charge'],
                    'levels' => ['error', 'warning','info'],
                    'logVars' => ['*'],
                    'logFile' => '@runtime/logs/charge.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['downloadFile'],
                    'levels' => ['error', 'warning','info'],
                    'logVars' => ['*'],
                    'logFile' => '@runtime/logs/downloadFile.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['zeroDownloadFile'],
                    'levels' => ['error', 'warning','info'],
                    'logVars' => ['*'],
                    'logFile' => '@runtime/logs/zeroDownloadFile.log',
                ],
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
        // ...
        'frontendWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'frontendWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'frontendWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_frontendWechatReturnUrl', // returnUrl 存储在会话中
        ],
        'businessWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'businessWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'businessWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_businessWechatReturnUrl', // returnUrl 存储在会话中
        ],
        'officialWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'officialWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'officialWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_officialWechatReturnUrl', // returnUrl 存储在会话中
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'on afterSend' => ['\common\components\GlobalApiLog','apiLog']
        ]
	],
	'modules' => [
		'delivery' => [
			'class' => 'business\modules\delivery\Delivery',
		]
	],
	'params' => $params,
];
