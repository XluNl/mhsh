<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'charset' => 'UTF-8',
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'traceLevel' => 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['*'],
                    'categories' => ['application']
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['group'],
                    'levels' => ['error','info','warning'],
                    'logVars' => ['*'],
                    'logFile' => '@runtime/logs/groupActive.log',
                ]
            ],
        ],
        'frontendWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'frontendWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'frontendWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_frontendWechatReturnUrl', // returnUrl 存储在会话中
        ],
        // 'db' => [
        //     'class' => 'yii\db\Connection',
        //     //'dsn' => 'sqlsrv:Server=localhost;Database=NewHBTY',
        //     'dsn' => 'mysql:host=localhost;dbname=mhsh-test',
        //     //'dsn' => 'sqlsrv:Server=120.26.109.182;Database=NewHBTY',
        //     //'dsn' => 'sqlsrv:Server=182.254.241.237;Database=NewHBTY',
        //     'username' => 'root',
        //     'password' => '123456',
        //     'charset' => 'utf8mb4',
        //     'tablePrefix' => 'sptx_',
        //     'enableSchemaCache' => true,
        //     'schemaCacheDuration' => 60,
        // ]
    ],
    'params' => $params,
];
