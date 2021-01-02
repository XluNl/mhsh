<?php

namespace common\models;

use common\utils\StringUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%goods_schedule_collection}}".
 *
 * @property integer $id
 * @property string $collection_name
 * @property integer $operation_id
 * @property string $operation_name
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $status
 * @property integer $owner_type
 * @property integer $owner_id
 * @property string $display_start
 * @property string $display_end
 * @property string $online_time
 * @property string $offline_time
 */
class GoodsScheduleCollection extends \yii\db\ActiveRecord
{
    public $owner_name;


    const DISPLAY_STATUS_WAITING = 1;
    const DISPLAY_STATUS_IN_SALE = 2;
    const DISPLAY_STATUS_SUSPEND = 3;
    const DISPLAY_STATUS_END = 4;

    public static $displayStatusTextArr = [
        self::DISPLAY_STATUS_WAITING => '未开始',
        self::DISPLAY_STATUS_IN_SALE => '销售中',
        self::DISPLAY_STATUS_SUSPEND => '已停止',
        self::DISPLAY_STATUS_END => '已结束',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_schedule_collection}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['collection_name', 'status','owner_id','owner_type'], 'required'],
            [['operation_id', 'company_id', 'status','owner_id','owner_type'], 'integer'],
            [['created_at', 'updated_at','display_start', 'display_end', 'online_time', 'offline_time'], 'safe'],
            [['display_start', 'display_end', 'online_time', 'offline_time'], 'validateDateTime'],
            [['collection_name', 'operation_name'], 'string', 'max' => 255],
            ['owner_type', 'in', 'range' => array_keys(GoodsConstantEnum::$ownerArr),'message' => '{attribute}不合法'],
            ['status','default','value' => CommonStatus::STATUS_ACTIVE]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'collection_name' => '排期名称',
            'operation_id' => '操作人ID',
            'operation_name' => '操作人名称',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'company_id' => 'Company ID',
            'status' => '状态',
            'display_start' => '展示开始时间',
            'display_end' => '展示结束时间',
            'online_time' => '起售时间',
            'offline_time' => '止售时间',
            'owner_type' => '归属类型',
            'owner_id' => '归属id',
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

    public function validateDateTime($attribute, $params)
    {
        if (!StringUtils::isBlank($this->display_start)&&!StringUtils::isBlank($this->display_end)){
            if ($this->display_end<=$this->display_start){
                $this->addError('display_start', '结束时间必须晚于开始时间');
                $this->addError('display_end', '结束时间必须晚于开始时间');
            }
        }
        if (!StringUtils::isBlank($this->online_time)&&!StringUtils::isBlank($this->offline_time)){
            if ($this->offline_time<=$this->online_time){
                $this->addError('online_time', '结束时间必须晚于开始时间');
                $this->addError('offline_time', '结束时间必须晚于开始时间');
            }
        }
        if (!StringUtils::isBlank($this->display_start)&&!StringUtils::isBlank($this->online_time)){
            if ($this->online_time<$this->display_start){
                $this->addError('online_time', '售卖开始时间必须不早于展示开始时间');
                $this->addError('display_start', '售卖开始时间必须不早于展示开始时间');
            }
        }
        if (!StringUtils::isBlank($this->display_end)&&!StringUtils::isBlank($this->offline_time)){
            if ($this->display_end<$this->offline_time){
                $this->addError('display_end', '售卖结束时间必须不晚于展示结束时间');
                $this->addError('offline_time', '售卖结束时间必须不晚于展示结束时间');
            }
        }
    }

    public function storeForm(){
        if (StringUtils::isBlank($this->display_start)){
            $this->display_start = $this->online_time;
        }
        if (StringUtils::isBlank($this->display_end)){
            $this->display_end = $this->offline_time;
        }
        return $this;
    }

    public function getSchedules(){
        return $this->hasMany(GoodsSchedule::className(),['collection_id' => 'id']);
    }
}
