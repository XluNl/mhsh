<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%system_options}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property string $option_name
 * @property string $option_field
 * @property string $option_value
 * @property integer $biz_type
 * @property integer $biz_id
 */
class SystemOptions extends \yii\db\ActiveRecord
{
    const BIZ_TYPE_SYSTEM = 1;
    const BIZ_TYPE_AGENT = 2;
    const BIZ_TYPE_DELIVERY = 3;

    const OPTION_FIELD_SYSTEM_TEST_VERSION = "SYSTEM_TEST_VERSION";
    const OPTION_FIELD_SYSTEM_MEIJIA_COOKIE = "SYSTEM_MEIJIA_COOKIE";
    const OPTION_FIELD_SYSTEM_AUTO_CANCEL_ORDER = "SYSTEM_AUTO_CANCEL_ORDER";
    const OPTION_FIELD_SYSTEM_ALLIANCE_AUTH_MONEY = "SYSTEM_ALLIANCE_AUTH_MONEY";
    const OPTION_FIELD_SYSTEM_ALLIANCE_STORE_NUM_FOR_NO_AUTH = "SYSTEM_ALLIANCE_STORE_NUM_FOR_NO_AUTH";
    const OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_NUM_FOR_NO_AUTH = "SYSTEM_ALLIANCE_GOODS_NUM_FOR_NO_AUTH";
    const OPTION_FIELD_SYSTEM_ALLIANCE_COUNT_FOR_NO_AUTH = "SYSTEM_ALLIANCE_COUNT_FOR_NO_AUTH";
    const OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_COUNT_FOR_NO_AUTH = "SYSTEM_ALLIANCE_GOODS_COUNT_FOR_NO_AUTH";
    const OPTION_FIELD_SYSTEM_ORDER_CLAIM_UP_RATIO = "SYSTEM_ORDER_CLAIM_UP_RATIO";
    const OPTION_FIELD_SYSTEM_DELIVERY_AUTH_MONEY = "SYSTEM_DELIVERY_AUTH_MONEY";
    const OPTION_FIELD_SYSTEM_LINGlLI_CONTROL = "SYSTEM_LINGlLI_CONTROL";

    public static $optionSystemArr=[
        self::OPTION_FIELD_SYSTEM_TEST_VERSION,
        self::OPTION_FIELD_SYSTEM_MEIJIA_COOKIE,
        self::OPTION_FIELD_SYSTEM_AUTO_CANCEL_ORDER,
        self::OPTION_FIELD_SYSTEM_ALLIANCE_AUTH_MONEY,
        self::OPTION_FIELD_SYSTEM_ALLIANCE_STORE_NUM_FOR_NO_AUTH,
        self::OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_NUM_FOR_NO_AUTH,
        self::OPTION_FIELD_SYSTEM_ALLIANCE_COUNT_FOR_NO_AUTH,
        self::OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_COUNT_FOR_NO_AUTH,
        self::OPTION_FIELD_SYSTEM_ORDER_CLAIM_UP_RATIO,

        self::OPTION_FIELD_SYSTEM_DELIVERY_AUTH_MONEY,
        self::OPTION_FIELD_SYSTEM_LINGlLI_CONTROL,
    ];

    public static $optionFieldArr=[
        self::OPTION_FIELD_SYSTEM_TEST_VERSION=>'测试版本号',
        self::OPTION_FIELD_SYSTEM_MEIJIA_COOKIE=>'美家cookie',
        self::OPTION_FIELD_SYSTEM_AUTO_CANCEL_ORDER=>'订单超时自动关闭时间(分)',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_AUTH_MONEY=>'联盟商家认证费用（元）',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_STORE_NUM_FOR_NO_AUTH=>'未认证联盟商家最大可开店次数',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_NUM_FOR_NO_AUTH=>'未认证联盟商家单店最大可上传商品数量',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_COUNT_FOR_NO_AUTH=>'联盟商户未交保证金可开店数量',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_COUNT_FOR_NO_AUTH=>'联盟商户未交保证金可发布商品数量',
        self::OPTION_FIELD_SYSTEM_ORDER_CLAIM_UP_RATIO=>'但商品赔付比例上限(%)',

        self::OPTION_FIELD_SYSTEM_DELIVERY_AUTH_MONEY=>'合伙人认证费用（元）',

        self::OPTION_FIELD_SYSTEM_LINGlLI_CONTROL=>'零里转移控制:不提醒(1)/提醒(2)/强制(3)',
    ];


    public static $optionValueArr=[
        self::OPTION_FIELD_SYSTEM_TEST_VERSION=>'2.0',
        self::OPTION_FIELD_SYSTEM_MEIJIA_COOKIE=>'{}',
        self::OPTION_FIELD_SYSTEM_AUTO_CANCEL_ORDER=>'15',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_AUTH_MONEY=>'1000',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_STORE_NUM_FOR_NO_AUTH=>'1',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_NUM_FOR_NO_AUTH=>'3',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_COUNT_FOR_NO_AUTH=>'1',
        self::OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_COUNT_FOR_NO_AUTH=>'3',
        self::OPTION_FIELD_SYSTEM_ORDER_CLAIM_UP_RATIO=>'50',
        self::OPTION_FIELD_SYSTEM_DELIVERY_AUTH_MONEY=>'2000',
        self::OPTION_FIELD_SYSTEM_LINGlLI_CONTROL=>'1',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%system_options}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['company_id', 'biz_type', 'biz_id'], 'integer'],
            [['option_name', 'option_field', 'option_value', 'biz_type', 'biz_id'], 'required'],
            [['option_name', 'option_field'], 'string', 'max' => 255],
            [['option_value'], 'string', 'max' => 4095],
            [['option_field', 'biz_type', 'biz_id'], 'unique', 'targetAttribute' => ['option_field', 'biz_type', 'biz_id', 'company_id'], 'message' => '去重'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'option_name' => '选项配置名称',
            'option_field' => '选项名',
            'option_value' => '内容',
            'biz_type' => '业务类型  1系统  2代理商 3配送点',
            'biz_id' => '业务id',
        ];
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}
