<?php
return [
    'adminEmail' => 'admin@example.com',
    'mdm.admin.configs' => [
        //'defaultUserStatus' => 10,
        'cacheDuration'=>'300',
        'userTable'=>'{{%admin_user}}'
    ],
    'role.exclude' =>[
        '登录用户',
        '系统正常运行管理员',
        '超级管理员',
        '支付管理权限',
        '验证码权限'
    ],
    'bsDependencyEnabled' => false, // this will not load Bootstrap CSS and JS for all Krajee extensions
   // 'bsVersion' => '4.x', // this will set globally `bsVersion` to Bootstrap 4.x for all Krajee Extensions
];
