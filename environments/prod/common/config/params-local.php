<?php
return [
    //为空默认所有ip均可访问
    'inner.service.info.white.ip.list'=>[
        "112.124.24.18",
        "118.31.167.204",
    ],
    'star.service.info.white.ip.list'=>[
        "47.114.45.121",
        "172.16.46.205"
    ],
    //是否展示星球模块
    'mini.program.star.show'=>true,
    'star.appid'=>'fd18997a50914b029f1591343554a11b',
    'officialWechat'=>[
        'wechatConfig' => [
            'app_id' => 'wx1de2fedd60a22ba3',
            'secret' => '969deb104305592c5ccdcb08a6f322dd',
            'token' => 'MHSH',
            'aes_key'=>'cBxVECrvGh0OZfkgeavFmzebl8aLQx26dobRzW83smk', // EncodingAESKey，兼容与安全模式下请一定要填写！！！
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => $_SERVER['DOCUMENT_ROOT'].'/../runtime/logs/officialWechat.log',
            ],
        ],

        // 微信支付配置 具体可参考EasyWechat
        'wechatPaymentConfig' => [
            // 必要配置
            'app_id' => 'wx1de2fedd60a22ba3',
            'mch_id'             => '1543153571',
            'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ],
    ],


    'frontendWechat'=>[
        // 微信支付配置 具体可参考EasyWechat
        'wechatPaymentConfig' => [
            // 必要配置
            'app_id' => 'wx1791fd2f9118b468',
            'mch_id'             => '1543153571',
            'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ],

        'wechatMiniProgramConfig' => [
            'app_id' => 'wx1791fd2f9118b468',
            'secret' => '528b46c3687c19849abbd31d8b79735b',
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => $_SERVER['DOCUMENT_ROOT'].'/../runtime/logs/frontendWechatMiniProgram.log',
            ],
        ],
    ],


    'businessWechat'=>[
        // 微信支付配置 具体可参考EasyWechat
        'wechatPaymentConfig' => [
            // 必要配置
            'app_id' => 'wx4bcd15bb5112e22d',
            'mch_id'             => '1543153571',
            'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ],

        'wechatMiniProgramConfig' => [
            'app_id' => 'wx4bcd15bb5112e22d',
            'secret' => '7855b743e0631b6c34825f3d33453056',
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => $_SERVER['DOCUMENT_ROOT'].'/../runtime/logs/businessWechatMiniProgram.log',
            ],
        ],
    ],

    'allianceWechat'=>[
        // 微信支付配置 具体可参考EasyWechat
        'wechatPaymentConfig' => [
            // 必要配置
            'app_id'             => 'wx8d37db829844ba3b',
            'mch_id'             => '1543153571',
            'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ],

        // 微信小程序配置 具体可参考EasyWechat
        'wechatMiniProgramConfig' => [
            'app_id' => 'wx8d37db829844ba3b',
            'secret' => '534ed48659c21c4563b143775d7da977',
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => $_SERVER['DOCUMENT_ROOT'].'/../runtime/logs/allianceWechatMiniProgram.log',
            ],
        ],
    ],

    //模板消息id列表
    'officialAccountTemplateIds'=>[
        //下单成功
        'buySuccessForNotifyDelivery'=>'calEXxpJhLa-cFYWhDQTC-LwdDPfH_RpQjjzaMcyT04',
        //通知用户取货
        'notifyCustomerToGet'=>'zvQ9bjFtt7MPCkZFJN0bcZPa22T13f845ox47ySH56I',
    ]
];
