<?php
return [
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
            'app_id' => 'wx207edf0ff9dda7f0',
            'secret' => '8e24e661a72263347a14e0f5c53ae886',
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
            'app_id' => 'wx207edf0ff9dda7f0',
            'mch_id'             => '1552258221',
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
            'app_id' => 'wxb52abbc4fc2b3cb5',
            'mch_id'             => '1552258221',
            'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ],

        'wechatMiniProgramConfig' => [
            'app_id' => 'wxb52abbc4fc2b3cb5',
            'secret' => 'b71c48d9eb5ef1c8723d4a3f064d266f',
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
            'app_id' => 'wx3822a16b495c2f13',
            'mch_id'             => '1552258221',
            'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ],

        'wechatMiniProgramConfig' => [
            'app_id' => 'wx3822a16b495c2f13',
            'secret' => 'e939925400b6f4ee711506782b6d5407',
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
            'app_id'             => 'wxf1361e2a9c0ebbe7',
            'mch_id'             => '1552258221',
            'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ],

        // 微信小程序配置 具体可参考EasyWechat
        'wechatMiniProgramConfig' => [
            'app_id' => 'wxf1361e2a9c0ebbe7',
            'secret' => 'a1b15bce1100abee9f784115039030cb',
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
        'buySuccessForNotifyDelivery'=>'2SbakHnDuuUtJt4V9i1iFvV13s8wsLB_YQGEYe9RqjM	',
        //通知用户取货
        'notifyCustomerToGet'=>'JTeEW6R6QLozONz9S0DW0vtGj3xne14goAE7RlxCf6c	',
    ]
];
