<?php

namespace common\models;

use common\utils\StringUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_invitation_activity}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $status
 * @property string $name
 * @property string $image
 * @property string $detail
 * @property string $show_start_time
 * @property string $show_end_time
 * @property integer $settle_status
 * @property string $settle_time
 * @property string $expect_settle_time
 * @property integer $settle_operator_id
 * @property string $settle_operator_name
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $type
 * @property string $activity_start_time
 * @property string $activity_end_time
 * @property integer $company_id
 * @property integer $version
 */
class CustomerInvitationActivity extends \yii\db\ActiveRecord
{
    const BIZ_STATUS_UN_START = 1;
    const BIZ_STATUS_RUNNING = 2;
    const BIZ_STATUS_UN_SETTLED = 3;
    const BIZ_STATUS_SETTLED = 4;

    public static $bizStatusArr = [
        self::BIZ_STATUS_UN_START=>'预热中',
        self::BIZ_STATUS_RUNNING=>'进行中',
        self::BIZ_STATUS_UN_SETTLED=>'待结算',
        self::BIZ_STATUS_SETTLED=>'已结算',
    ];

    const VALIDATE_FALSE =0;
    const VALIDATE_TRUE =1;

    public static $validateArr = [
        self::VALIDATE_FALSE=>'无效',
        self::VALIDATE_TRUE=>'有效',
    ];

    public static $validateCssArr = [
        self::VALIDATE_FALSE=>'label label-danger',
        self::VALIDATE_TRUE=>'label label-success',
    ];

    const TYPE_INVITATION = 1;
    public static $typeArr=[
        self::TYPE_INVITATION=>'拉新活动',
    ];
    public static $typeCssArr=[
        self::TYPE_INVITATION=>'label label-success',
    ];

    const SETTLE_STATUS_UN_DEAL = 0;
    const SETTLE_STATUS_DEAL = 1;
    public static $settleStatusArr=[
        self::SETTLE_STATUS_UN_DEAL=>'未结算',
        self::SETTLE_STATUS_DEAL=>'已结算',
    ];
    public static $settleStatusCssArr=[
        self::SETTLE_STATUS_UN_DEAL=>'label label-success',
        self::SETTLE_STATUS_DEAL=>'label label-warning',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_invitation_activity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'show_start_time', 'show_end_time', 'settle_time', 'activity_start_time', 'activity_end_time','expect_settle_time'], 'safe'],
            [['detail'], 'string'],
            [['settle_operator_id', 'operator_id', 'type','company_id','status', 'settle_status','version'], 'integer'],
            [['name', 'image', 'settle_operator_name', 'operator_name'], 'string', 'max' => 255],
            [['activity_start_time', 'activity_end_time','show_start_time', 'show_end_time','expect_settle_time'], 'validateDateTime'],
        ];
    }

    public function validateDateTime($attribute, $params)
    {
        if (!StringUtils::isBlank($this->activity_start_time)&&!StringUtils::isBlank($this->activity_end_time)){
            if ($this->activity_end_time<=$this->activity_start_time){
                $this->addError('activity_start_time', '活动统计结束时间必须晚于开始时间');
                $this->addError('activity_end_time', '活动统计结束时间必须晚于开始时间');
            }
        }
        if (StringUtils::isNotBlank($this->expect_settle_time)&&StringUtils::isNotBlank($this->activity_end_time)){
            if ($this->expect_settle_time<=$this->activity_end_time){
                $this->addError('activity_end_time', '活动结算时间必须晚于活动统计结束时间');
                $this->addError('expect_settle_time', '活动结算时间必须晚于活动统计结束时间');
            }
        }
        if (!StringUtils::isBlank($this->show_start_time)&&!StringUtils::isBlank($this->show_end_time)){
            if ($this->show_end_time<=$this->show_start_time){
                $this->addError('show_end_time', '活动展示结束时间必须晚于开始时间');
                $this->addError('show_start_time', '活动展示结束时间必须晚于开始时间');
            }
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
            'status' => '活动状态',
            'name' => '活动名称',
            'image' => '活动主图',
            'detail' => '活动详情',
            'show_start_time' => '活动展示开始时间',
            'show_end_time' => '活动展示结束时间',
            'settle_status' => '结算状态',
            'settle_time' => '结算时间',
            'settle_operator_id' => '结算人id',
            'settle_operator_name' => '结算人姓名',
            'operator_id' => '操作人id',
            'operator_name' => '操作人姓名',
            'type' => '活动类型',
            'activity_start_time' => '活动统计开始时间',
            'activity_end_time' => '活动统计结束时间',
            'expect_settle_time' => '活动预计结算时间',
            'company_id'=>'公司id',
            'version'=>'版本号',
        ];
    }

    public function optimisticLock()
    {
        return "version";
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
