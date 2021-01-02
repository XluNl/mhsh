<?php

namespace common\models;

use backend\services\CustomerInvitationActivityPrizeService;
use common\utils\PriceUtils;
use common\utils\StringUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_invitation_activity_prize}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $activity_id
 * @property integer $type
 * @property string $name
 * @property string $batch_no
 * @property integer $num
 * @property integer $status
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $range_start
 * @property integer $range_end
 * @property integer $expect_quantity
 * @property integer $real_quantity
 * @property integer $company_id
 * @property integer $level_type
 */
class CustomerInvitationActivityPrize extends \yii\db\ActiveRecord
{
    const TYPE_COUPON = 1;
    const TYPE_BONUS = 2;
    const TYPE_OTHER = 3;
    public static $typeArr =[
        self::TYPE_COUPON => '券',
        self::TYPE_BONUS => '奖励金',
        self::TYPE_OTHER=>'线下实物',
    ];
    public static $typeCssArr=[
        self::TYPE_COUPON=>'label label-success',
        self::TYPE_BONUS=>'label label-info',
        self::TYPE_OTHER=>'label label-primary',
    ];

    const LEVEL_TYPE_ONE =1;
    const LEVEL_TYPE_TWO =2;
    public static $levelTypeArr=[
        self::LEVEL_TYPE_ONE=>'一级邀请奖品',
        self::LEVEL_TYPE_TWO=>'二级邀请奖品',
    ];

    public static $levelTypeCssArr=[
        self::LEVEL_TYPE_ONE=>'label label-success',
        self::LEVEL_TYPE_TWO=>'label label-info',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_invitation_activity_prize}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['activity_id', 'name','batch_no','num','range_start','range_end','level_type'], 'required'],
            [['activity_id', 'type', 'status', 'operator_id', 'range_start', 'range_end', 'expect_quantity', 'real_quantity','company_id','level_type'], 'integer'],
            [[ 'num' ], 'number'],
            [['name', 'operator_name','batch_no'], 'string', 'max' => 255],
            [['range_start','range_end'], 'validateRange'],
            ['num','validateNum'],
            ['batch_no','validateBatchNo'],
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
            'activity_id' => '活动id',
            'type' => '活动奖品类型',
            'name' => '奖品名称',
            'num' => '单次数量',
            'status' => '状态',
            'operator_id' => '操作人id',
            'operator_name' => '操作人姓名',
            'range_start' => '范围起始',
            'range_end' => '范围截止',
            'expect_quantity' => '预计分发数量',
            'real_quantity' => '实际分发数量',
            'batch_no'=>'奖品批次',
            'company_id'=>'公司id',
            'level_type'=>'奖励等级',
        ];
    }


    public function validateRange($attribute,$params){
        if (!StringUtils::isBlank($this->range_start)&&!StringUtils::isBlank($this->range_end)){
            if ($this->range_start>$this->range_end){
                $this->addError('range_start', '范围区间不正确');
                $this->addError('range_end', '范围区间不正确');
            }
        }
    }


    public function validateBatchNo($attribute,$params){
        if (!StringUtils::isBlank($this->type)&&!StringUtils::isBlank($this->batch_no)){
            if (!CustomerInvitationActivityPrizeService::validateBatchNo($this->type,$this->batch_no,$this->company_id)){
                $this->addError('batch_no', '批次不存在');
            }
        }
    }

    public function validateNum($attribute,$params){
        if (self::TYPE_COUPON==$this->type){
            if (StringUtils::isNotBlank($this->num))
            {
                if(!preg_match("/^[1-9][0-9]*$/" ,$this->num)){
                    $this->addError($attribute, "必须为正整数");
                }
            }
        }
        else if (self::TYPE_BONUS==$this->type){
            if (PriceUtils::validateInput($this->num) === false)
            {
                $this->addError($attribute, "最小精确到分(0.01)");
            }
        }
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
    public function formatForm(){
        if (self::TYPE_BONUS==$this->type){
            $this->num = Common::setAmount($this->num);
            $this->expect_quantity = Common::setAmount($this->expect_quantity);
        }
        return $this;
    }

    public function restoreForm(){
        if (self::TYPE_BONUS==$this->type){
            $this->num = Common::showAmount($this->num);
            $this->expect_quantity = Common::showAmount($this->expect_quantity);
        }
        return $this;
    }
}
