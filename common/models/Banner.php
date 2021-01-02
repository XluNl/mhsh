<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%banner}}".
 *
 * @property int $id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int $company_id
 * @property int $type 类型  1异业联盟首页 2平台自营
 * @property int $sub_type 1普通 2,url跳转 3一个排期 4多个排期
 * @property string|null $name 资源位名称
 * @property string|null $images 图片
 * @property string|null $messages 文本
 * @property int|null $display_order 排序
 * @property string|null $link_info 关联信息
 * @property int|null $operator_id 操作人id
 * @property string|null $operator_name 操作人名称
 * @property string $online_time 上线时间
 * @property string $offline_time 下线时间
 * @property int $status 状态  1启用 0禁用  -1删除
 */
class Banner extends \yii\db\ActiveRecord
{
    const TYPE_OWNER_SELF = 1;
    const TYPE_ALLIANCE = 2;
    public static $typeArr=[
        self::TYPE_OWNER_SELF=>'自营位',
        self::TYPE_ALLIANCE=>'联盟位'
    ];
    public static $typeCssArr=[
        self::TYPE_ALLIANCE=>'label label-info',
        self::TYPE_OWNER_SELF=>'label label-success',
    ];

    const SUB_TYPE_DEFAULT = 1;
    const SUB_TYPE_URL_JUMP = 2;
    const SUB_TYPE_SCHEDULE_DETAIL = 3;
    const SUB_TYPE_SCHEDULE_LIST = 4;
    public static $subTypeArr=[
        self::SUB_TYPE_DEFAULT=>'普通类型',
        self::SUB_TYPE_URL_JUMP=>'链接跳转',
        self::SUB_TYPE_SCHEDULE_DETAIL=>'单品详情',
        self::SUB_TYPE_SCHEDULE_LIST=>'商品列表'
    ];

    public static $subTypeCssArr=[
        self::SUB_TYPE_DEFAULT=>'label label-info',
        self::SUB_TYPE_URL_JUMP=>'label label-primary',
        self::SUB_TYPE_SCHEDULE_DETAIL=>'label label-success',
        self::SUB_TYPE_SCHEDULE_LIST=>'label label-warning'
    ];



    public $url_skip;
    public $schedule_one;
    public $schedule_mut;
    public $link_info_restore;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%banner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'online_time', 'offline_time','sub_type','link_info','schedule_one','schedule_mut','url_skip'], 'safe'],
            [['company_id', 'type', 'display_order', 'operator_id', 'status','sub_type'], 'integer'],
            [['images', 'messages'], 'string'],
            ['url_skip', 'url', 'defaultScheme' => 'http'],
            [['online_time', 'offline_time'], 'required'],
            [['type'],'checkLinkInfo'],
            [['name', 'operator_name','link_info'], 'string', 'max' => 255],
        ];
    }

    public function checkLinkInfo($attribute, $params){
        if($this->sub_type == self::SUB_TYPE_URL_JUMP && empty($this->url_skip)){
            $this->addError('url_skip', '跳转链接不能为空');return;
        }
        if($this->sub_type == self::SUB_TYPE_SCHEDULE_DETAIL && empty($this->schedule_one)){
            $this->addError('schedule_one', '请添加关联商品');return;
        }
        if($this->sub_type == self::SUB_TYPE_SCHEDULE_LIST && empty($this->schedule_mut)){
            $this->addError('schedule_mut', '请添加至少一个商品');return;
        }
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
            'type' => '归属类型',//  1异业联盟首页 2平台自营
            'sub_type' => '类型',//1普通 2,url跳转 3一个排期 4多个排期,
            'name' => '资源位名称',
            'images' => '图片',
            'messages' => '文本',
            'display_order' => '排序',
            'link_info' => '关联信息',
            'operator_id' => '操作人id',
            'operator_name' => '操作人名称',
            'online_time' => '上线时间',
            'offline_time' => '下线时间',
            'status' => '状态',
        ];
    }

    public function storeForm(){
        if($this->sub_type == self::SUB_TYPE_DEFAULT){
            $this->link_info = '';
        }
        if($this->sub_type == self::SUB_TYPE_URL_JUMP){
            $this->link_info = $this->url_skip;
        }
        if($this->sub_type == self::SUB_TYPE_SCHEDULE_DETAIL){
            $this->link_info = $this->schedule_one;
        }
        if($this->sub_type == self::SUB_TYPE_SCHEDULE_LIST){
            $this->link_info = json_encode(array_unique($this->schedule_mut??[]));
        }
        return $this;
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
