<?php

return [
    'language'=>'zh-CN',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'aliases'=>[

    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ]
    ],
];
