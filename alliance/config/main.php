<?php
$params = array_merge(
	require (__DIR__ . '/../../common/config/params.php'),
	require (__DIR__ . '/../../common/config/params-local.php'),
	require (__DIR__ . '/params.php'),
	require (__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-alliances',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'alliance\controllers',
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
        'allianceWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'allianceWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'allianceWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_allianceWechatReturnUrl', // returnUrl 存储在会话中
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'on afterSend' => ['\common\components\GlobalApiLog','apiLog']
        ]
	],
	'modules' => [
		'alliance' => [
			'class' => 'alliance\modules\alliance\Alliance',
		],

	],
	'params' => $params,
];
