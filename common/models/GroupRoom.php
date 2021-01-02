<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%group_room}}".
 *
 * @property int $id
 * @property string $active_no 团购活动no
 * @property string $room_no 拼团房间no
 * @property int $team_id 团长id
 * @property string $team_name 团长名称
 * @property int $continued 持续时间(分钟)
 * @property string $expect_finished_at  预计截团时间
 * @property int|null $place_count 下单数
 * @property int $paid_order_count 支付数
 * @property int|null $max_level 最大成团数
 * @property int $min_level 最小成团数
 * @property int $status 拼团状态
 * @property string|null $finished_at 结束时间
 * @property string|null $msg 备注
 * @property string $created_at
 * @property string $updated_at
 * @property int $company_id 公司id
 */
class GroupRoom extends \yii\db\ActiveRecord
{

    const CAN_SHARE = true;
    const CAN_NOT_SHARE = false;
    public static $canShareArr = [
        self::CAN_SHARE =>'可以分享',
        self::CAN_NOT_SHARE =>'不可分享',
    ];


    const DISPLAY_STATUS_PROCESSING = 0;
    const DISPLAY_STATUS_SUCCESS = 1;
    const DISPLAY_STATUS_FAILED = 2;
    const DISPLAY_STATUS_REMAINING = 3;

    public static $displayStatusTextForReadyBuyer = [
        self::DISPLAY_STATUS_PROCESSING =>'我要参团',
        self::DISPLAY_STATUS_SUCCESS =>'人数已满',
        self::DISPLAY_STATUS_FAILED=>'下回赶早',
        self::DISPLAY_STATUS_REMAINING=>'还有机会',
    ];


    const GROUP_STATUS_PROCESSING = 0;
    const GROUP_STATUS_SUCCESSFUL = 1;
    const GROUP_STATUS_FAILED = 2;
    public static $groupRoomStatus = [
        self::GROUP_STATUS_PROCESSING =>'拼团进行中',
        self::GROUP_STATUS_SUCCESSFUL =>'拼团成功',
        self::GROUP_STATUS_FAILED=>'拼团失败',
    ];

    public static $groupRoomStatusCss = [
        self::GROUP_STATUS_PROCESSING =>'btn btn-info btn-xs',
        self::GROUP_STATUS_SUCCESSFUL =>'btn btn-success btn-xs',
        self::GROUP_STATUS_FAILED =>'btn btn-danger btn-xs'
    ];


    const GROUP_ORDER_TYPE_NEW = 0;
    const GROUP_ORDER_TYPE_JOIN = 1;
    public static $groupOrderTypeArr = [
        self::GROUP_ORDER_TYPE_NEW => '创建团',
        self::GROUP_ORDER_TYPE_JOIN => '加入团'
    ];

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

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%group_room}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['active_no', 'room_no', 'team_name','company_id','continued'], 'required'],
            [['team_id', 'place_count', 'paid_order_count', 'max_level', 'min_level', 'status', 'company_id','continued'], 'integer'],
            [['finished_at', 'created_at', 'updated_at','expect_finished_at'], 'safe'],
            [['active_no', 'room_no'], 'string', 'max' => 50],
            [['team_name', 'msg'], 'string', 'max' => 255],
            [['room_no'], 'unique', 'message' => 'room_no不能重复'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active_no' => '团购活动编号',
            'room_no' => '拼团房间编号',
            'team_id' => '拼主',
            'team_name' => '拼主名称',
            'continued' =>'持续时间(分钟)',
            'expect_finished_at'=>'预计截团时间',
            'place_count' => '下单数',
            'paid_order_count' => '支付数',
            'max_level' => '最大成团数',
            'min_level' => '最小成团数',
            'status' => '拼团状态',
            'finished_at' => '结束时间',
            'msg' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => '公司id',
        ];
    }

    public function generateNo() {
        $yCode = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T'];
        return 'GR'.$yCode[intval(date('Y')) - 2020] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    }

 /*   public function getGroupRoomOrders(){
        return $this->hasMany(GroupRoomOrder::className(),['room_no'=>'room_no']);
    }*/

    public function getGroupRoomOrders(){
        return $this->hasMany(GroupRoomOrder::className(),['room_no'=>'room_no'])
            ->joinWith(['order'=>function($query){
                $query->where(['not in',"order_status",[Order::ORDER_STATUS_CANCELING,Order::ORDER_STATUS_CANCELED]]);
            }]);
    }

    public function getGroupActive(){
        return $this->hasOne(GroupActive::className(),['active_no'=>'active_no']);
    }

    public function getTeamInfo(){
        return $this->hasOne(Customer::className(),['id'=>'team_id']);
    }
}
