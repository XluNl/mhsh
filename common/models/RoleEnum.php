<?php


namespace common\models;


class RoleEnum
{
    const ROLE_ADMIN = 1;
    const ROLE_SYSTEM = 2;
    const ROLE_DELIVERY = 3;
    const ROLE_CUSTOMER = 4;
    const ROLE_POPULARIZER = 5;
    const ROLE_AGENT = 6;
    const ROLE_HA = 7;
    const ROLE_STORAGE = 8;

    public static $roleList =[
        self::ROLE_ADMIN => '管理员',
        self::ROLE_SYSTEM => '平台',
        self::ROLE_DELIVERY => '配送团长',
        self::ROLE_CUSTOMER => '普通用户',
        self::ROLE_POPULARIZER => '分享团长',
        self::ROLE_AGENT =>'代理商',
        self::ROLE_HA =>'异业联盟',
        self::ROLE_STORAGE=>'仓库',
    ];
}