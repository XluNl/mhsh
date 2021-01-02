<?php


namespace common\models;


class CommonStatus
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;
    const STATUS_DELETED = -1;

    public static $activeStatusArr=[
        Customer::STATUS_ACTIVE ,
        Customer::STATUS_DISABLED,
    ];

    public static $StatusArr=[
        Customer::STATUS_ACTIVE => '启用',
        Customer::STATUS_DISABLED => '禁用',
    ];

    public static $StatusCssArr=[
        Customer::STATUS_ACTIVE => 'label label-success',
        Customer::STATUS_DISABLED => 'label label-danger',
    ];

}