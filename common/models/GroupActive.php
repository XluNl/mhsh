<?php

namespace common\models;

use common\services\GoodsScheduleService;
use common\utils\StringUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%group_active}}".
 *
 * @property int $id id
 * @property string $active_no 活动编号
 * @property int $schedule_id 排期id
 * @property int $continued 持续时间
 * @property string $rule_desc 规则
 * @property int $status 拼团状态 0删除 1上线  2下线
 * @property int $operator_id 操作人
 * @property string $operator_name 操作人
 * @property int $company_id 公司id
 * @property int $owner_type 归属（平台，异业联盟，团长自营）
 * @property string $created_at
 * @property-read mixed $adminInfo
 * @property-read mixed $activeRoom
 * @property-read mixed $schedule
 * @property string $updated_at
 */
class GroupActive extends ActiveRecord
{   

    public $rule;
    public $pic_icon;

    const STATUS_DELETED = 0;
    const STATUS_UP = 1;
    const STATUS_DOWN = 2;

    public static $statusArr=[
        self::STATUS_DELETED=>'删除',
        self::STATUS_UP=>'上线',
        self::STATUS_DOWN=>'下线',
    ];

    public static $statusCssArr=[
        self::STATUS_DELETED=>'label label-danger',
        self::STATUS_UP=>'label label-success',
        self::STATUS_DOWN=>'label label-warning',
    ];

    public static $activeStatusArr=[
        self::STATUS_UP,
        self::STATUS_DOWN,
    ];


    const ACTIVE_NO_STARTED = 1;
    const ACTIVE_PRO = 2;
    const ACTIVE_END = 3;
    public static $activeStatus = [
        self::ACTIVE_NO_STARTED =>'未开始',
        self::ACTIVE_PRO =>'进行中',
        self::ACTIVE_END =>'已结束'
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%group_active}}';
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['schedule_id','owner_type', 'company_id','continued','rule_desc'], 'required'],
            [['schedule_id','continued', 'operator_id', 'company_id','owner_type'], 'integer'],
            [['operator_id','company_id'],'default','value' => 0],
            [['source','rule'], 'safe'],
            ['continued', 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['rule_desc'],'checkRules'],
            [['rule_desc','operator_name'], 'string', 'max' => 225],
            [['active_no'], 'string', 'max' => 32],
        ];
    }

    public function generate_no() {
        $yCode = array('GA', 'GB', 'GC', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GJ', 'GK', 'GL', 'GM', 'GN');
        return $yCode[intval(date('Y')) - 2020] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%04d', rand(0, 9999));
    }

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord){
                $this->active_no = $this->generate_no();
            }
            return true;
        } else {
            return false;
        }
    }

    public function checkRules($attribute, $params){
        $price2 = true;
        $price3 = true;
        $price4 = true;
        $price5 = true;
        $rules = Json::decode($this->rule_desc);

        foreach ($rules as $key => $value) {
            $index = $key+2;

            if($index==2 && empty($value)){
                $price2 = false;
            }
            if($index==3 && empty($value)){
                $price3 = false;
            }
            if($index==4 && empty($value)){
                $price4 = false;
            }
            if($index==5 && empty($value)){
                $price5 = false;
            }
        }
        if(!$price2 && !$price3 && !$price4 && !$price5){
            $this->addError('rule', '请按顺序至少填写一组');
        }
        if($price3 && !$price2){
             $this->addError('rule', '请按顺序填写规则');
        }
        if($price4 && (!$price2 ||!$price3)){
             $this->addError('rule', '请按顺序填写规则');
        }
        if($price5 && (!$price2 || !$price3 || !$price4)){
             $this->addError('rule', '请按顺序填写规则');
        }

        if ($this['schedule']==null){
            $this->addError('schedule_id', '请先选择一个商品');
        }
        else{
            foreach ($rules as $rule){
                if ($rule>$this['schedule']['price']){
                    $this->addError('rule', '团购价格不得高于排期价格');
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'active_no' => '活动编号',
            'schedule_id' => '排期id',
            'continued' => '持续时间',
            'rule_desc' => '规则',
            'status' => '活动状态',//拼团状态 1未开始  2进行中 3已结束
            'operator_id' => '操作人id',
            'operator_name' => '操作人',
            'company_id' => '公司id',
            'owner_type' => '归属类型',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    public function storeForm(){
        $rules = $this->rule_desc;
        foreach ($rules as $k => $v) {
            if (StringUtils::isNotBlank($v)){
                $rules[$k] = Common::setAmount($v);
            }
        }
        $this->rule_desc = Json::encode($rules);
        return $this;
    }

    public function restoreForm(){
        $rules = Json::decode($this->rule_desc);
        $temp = [];
        foreach ($rules as $k => $v) {
            if (StringUtils::isNotBlank($v)){
                $v =  Common::showAmount($v);
            }
            $temp['price'.($k)] = $v;
        }
        $this->rule = 1;
        $this->rule_desc = $temp;
        return $this;
    }

    public function getActiveRoom(){
        return $this->hasMany(GroupRoom::className(),['active_id'=>'id']);
    }

    public function getSchedule(){
        return $this->hasOne(GoodsSchedule::className(),['id'=>'schedule_id']);
    }

    public function getAdminInfo(){
        return $this->hasOne(AdminUser::className(),['id'=>'operator_id']);
    }

}
