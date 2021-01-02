<?php
return [
    'adminEmail' => 'admin@example.com',
    'test_version'=> '2.4',

    // 微信支付配置 具体可参考EasyWechat
    'wechatPaymentConfig' => [
        // 必要配置
        'app_id'             => 'wx1791fd2f9118b468',
        'mch_id'             => '1543153571',
        'key'                => 'dWvIRswgpocuZfzflSgG0qCV2gU1KNjE',   // API 密钥

        // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
        'cert_path'          => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
        'key_path'           => dirname(dirname(dirname(__FILE__))).'/cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

        'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
    ],

    // 微信小程序配置 具体可参考EasyWechat
    'wechatMiniProgramConfig' => [
        'app_id' => 'wx1791fd2f9118b468',
        'secret' => '528b46c3687c19849abbd31d8b79735b',
        // 下面为可选项
        // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
        'response_type' => 'array',

        'log' => [
            'level' => 'debug',
            'file' => __DIR__.'/../runtime/logs/wechatMiniProgram.log',
        ],
    ],

];
