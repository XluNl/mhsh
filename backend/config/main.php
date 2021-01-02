<?php

use common\models\Common;

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'HBTYZYKJ',
    'name'=>'杭州满好生活网络科技有限公司',
    'language' => 'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log','cdn'],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'common\models\AdminUser',
            'enableAutoLogin' => true,
            'identityCookie' => [
                //'domain' => '.'.Common::getFirstHost(),
                'path' => '/',
                'name' => '_identity',
                'httpOnly' => true,
            ],
            'loginUrl' => ['admin\user\login'],
            'authTimeout'=>900,
        ],
        'session' => [
            'class' => 'yii\web\CacheSession',
//               'timeout' => 1,
//             'cookieMode' =>'only',
//             'cookieParams' => array('secure' => false, 'httponly' => false),
           // 'cookieParams' => ['domain' => '.'.Common::getFirstHost() , 'lifetime' => 900],
        ],
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

        'allianceWechat' => [
            'class' => 'common\components\wechat\Wechat',
            'configPrefix' => 'allianceWechat',
            'userOptions' => [],  // 用户身份类参数
            'sessionParam' => 'allianceWechatUser', // 微信用户信息将存储在会话在这个密钥
            'returnUrlParam' => '_allianceWechatReturnUrl', // returnUrl 存储在会话中
        ],

        //自定义图片上传类
        'imgload' => [
            'class' => 'backend\components\Upload'
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager', // 使用数据库管理配置文件
            'defaultRoles' => ['登录用户'],
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error','warning','info'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
            'class' => 'backend\components\ExceptionHandler',//（这里配置我们自己写的异常处理方法）
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        //'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'kartik\form\ActiveFormAsset' => [
                    'bsDependencyEnabled' => false // do not load bootstrap assets for a specific asset bundle
                ],
                'dmstr\web\AdminLteAsset' => [
                    'skin' => '',
                ],
                'nullref\datatable\assets\DataTableAsset' => [
                    'styling' => \nullref\datatable\assets\DataTableAsset::STYLING_BOOTSTRAP,
                ]
            ],
        ],
        'cdn' => [
            'class' => 'yiizh\cdn\CDN',
            'assets' => [
                [
                    'class' => 'yii\web\JqueryAsset',
                    'js' => [
                        'https://cdn.bootcss.com/jquery/2.2.4/jquery.min.js',
                        'https://cdn.bootcss.com/jquery-migrate/1.3.0/jquery-migrate.min.js',
                    ]
                ],
                [
                    'class' => 'yii\bootstrap\BootstrapAsset',
                    'css' => [
                        'https://cdn.bootcss.com/twitter-bootstrap/3.3.7/css/bootstrap.min.css',
                    ],
                ],
                [
                    'class' => 'yii\bootstrap\BootstrapPluginAsset',
                    'js'=>[
                        'https://cdn.bootcss.com/twitter-bootstrap/3.3.7/js/bootstrap.min.js'
                    ],
                ],
                
                [
                    'class' => 'yii\bootstrap\BootstrapThemeAsset',
                    'css' => [
                         'https://cdn.bootcss.com/twitter-bootstrap/3.3.7/css/bootstrap-theme.min.css',
                    ],
                ],
                [
                    'class' => 'rmrevin\yii\fontawesome\AssetBundle',
                    'css' => [
                        'https://cdn.bootcss.com/font-awesome/4.6.3/css/font-awesome.min.css',
                    ],
                ],
                [
                    'class' => 'backend\assets\BootboxAsset',
                    'js' => [
                        'https://cdn.bootcss.com/bootbox.js/4.4.0/bootbox.min.js'
                    ]
                ],
                [
                    'class' => 'backend\assets\JqueryDateTimePickerAsset',
                    'js'=>[
                        'https://cdn.bootcss.com/jquery-datetimepicker/2.5.3/build/jquery.datetimepicker.full.min.js'
                    ],
                    'css'=>[
                        'https://cdn.bootcss.com/jquery-datetimepicker/2.5.3/jquery.datetimepicker.min.css'
                    ],
                ],
                [
                    'class' => 'backend\assets\ICheckAsset',
                    'js'=>[
                        'https://cdn.bootcss.com/iCheck/1.0.2/icheck.min.js'
                    ],
                    'css'=>[
                        'https://cdn.bootcss.com/iCheck/1.0.2/skins/all.css'
                    ],
                ],
                [
                    'class' => 'backend\assets\EchartsAsset',
                    'js'=>[
                        'https://cdn.bootcss.com/echarts/3.2.3/echarts.min.js'
                    ],
                ],
                [
                    'class' => 'dmstr\web\AdminLteAsset',
                    'css'=>[
                        'https://cdn.bootcss.com/admin-lte/2.4.17/css/AdminLTE.min.css',
                        'https://cdn.bootcss.com/admin-lte/2.4.17/css/skins/_all-skins.min.css',

                    ],
                    'js'=>[
                        'https://cdn.bootcss.com/admin-lte/2.4.17/js/adminlte.min.js'
                    ],
                ],
                [
                    'class' => 'backend\assets\LayDateAsset',
                    'js'=>[
                        'https://www.layuicdn.com/layDate-v5.0.9/laydate.js'
                    ],
                ],
                [
                    'class' => 'backend\assets\BootstrapTableAsset',
                    'css'=>[
                        'https://cdn.bootcss.com/bootstrap-table/1.11.1/bootstrap-table.min.css',
                    ],
                    'js'=>[
                        'https://cdn.bootcss.com/bootstrap-table/1.11.1/bootstrap-table.min.js',
                        'https://cdn.bootcss.com/bootstrap-table/1.11.1/locale/bootstrap-table-zh-CN.min.js',
                        'https://cdn.bootcss.com/bootstrap-table/1.11.1/extensions/export/bootstrap-table-export.min.js',
                        'https://cdn.bootcss.com/TableExport/5.2.0/js/tableexport.min.js',
                    ],
                ],
                [
                    'class' => 'backend\assets\BootstrapDateTimePickerAsset',
                    'css'=>[
                        'https://cdn.bootcss.com/smalot-bootstrap-datetimepicker/2.4.4/css/bootstrap-datetimepicker.css'
                    ],
                    'js'=>[
                        'https://cdn.bootcss.com/smalot-bootstrap-datetimepicker/2.4.4/js/bootstrap-datetimepicker.min.js',
                        'https://cdn.bootcss.com/smalot-bootstrap-datetimepicker/2.4.4/js/locales/bootstrap-datetimepicker.zh-CN.js',
                    ],
                ],

            ]
        ],
         
    ],
    'params' => $params,
    'modules' => [
        'admin' => [
            'class' => 'mdm\admin\Module',
            //'layout' => 'left-menu',//yii2-admin的导航菜单
        ],
        'redactor' => [
            'class' => 'yii\redactor\RedactorModule',
            'uploadDir' => dirname(dirname(__DIR__)) . '/public/uploads/detail',
            'uploadUrl' => '@publicImageUrl/uploads/detail',
            'imageAllowExtensions'=>['jpg','png','gif']
        ],
        'datecontrol' =>  [
            'class' => '\kartik\datecontrol\Module'
        ],

        'gridview' => ['class' => 'kartik\grid\Module'],

    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'mini-proxy/*',
            'session/*',
            'admin/user/login',
            'admin/user/reset-password',
            'admin/user/request-password-reset',
            'admin/user/captcha',
            'admin/*',
            'gii/*',
            //'debug/*',
            'api-log/*'

        ]
    ],
    
];
