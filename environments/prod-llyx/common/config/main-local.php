<?php

use common\models\Captcha;

Yii::setAlias('@publicImageUrl', 'https://image.lingliyouxuan.cn/');
return [
    'aliases'=>[
        '@storeUrl'=>'https://store.manhaoshenghuo.cn/',
        '@starUrl'=>'https://star.grpu.com.cn/api/',
        '@lingLiUrl'=>'https://mall.llyx.ink/',
    ],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp159i4z6gmd598ts5m.mysql.rds.aliyuncs.com;dbname=llyx',
            'username' => 'llyx',
            'password' => 'CwrKrCXmabkZPJre',
            'charset' => 'utf8mb4',
            'tablePrefix' => 'sptx_',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 60,
        ],
        //自定义图片上传类
        'fileDomain' => [
            'class' => 'common\components\FileDomain',
            'fileDomain' =>  Yii::getAlias("@publicImageUrl"),
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.163.com',
                'username' => 'hzg7@163.com',
                'password' => '15068501736',
                'port' => '25',
                'encryption' => 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['hzg7@163.com' => '杭州零里优选有限公司'],
            ],
        ],
        'sms' => [
            'class' => 'common\components\SMS',
            'apikey' => 'faa08952e81d19068dfe8c96cf7cf3fa',
            'company' => '零里优选',
            'tpl_list' => [
                Captcha::SORT_SMS_CUSTOMER => ['tpl_id' => 3991166, 'title' => '注册模板'],
                Captcha::SORT_SMS_BUSINESS => ['tpl_id' => 3991166, 'title' => '注册模板'],
                Captcha::SORT_SMS_ALLIANCE => ['tpl_id' => 3991166, 'title' => '注册模板'],
            ],
        ],
    ],
];
