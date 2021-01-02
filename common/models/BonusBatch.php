<?php

namespace common\models;

use common\utils\StringUtils;
use common\utils\UUIDUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%bonus_batch}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property string $batch_no
 * @property string $name
 * @property integer $type
 * @property string $remark
 * @property string $draw_start_time
 * @property string $draw_end_time
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $status
 * @property integer $draw_amount
 * @property integer $amount
 * @property integer $version
 */
class BonusBatch extends \yii\db\ActiveRecord
{

    const TYPE_CASH = 1;

    public static $typeArr =[
        self::TYPE_CASH=>'现金',
    ];

    public static  $typeCssArr=[
        self::TYPE_CASH=>'label label-success',
    ];

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;

    public static $statusArr = [
        self::STATUS_DELETED =>'已删除',
        self::STATUS_ACTIVE =>'已激活',
        self::STATUS_DISABLED =>'已停止',
    ];

    public static $statusDisplayArr = [
        self::STATUS_ACTIVE =>'已激活',
        self::STATUS_DISABLED =>'已停止',
    ];
    public static $statusCssArr = [
        self::STATUS_DELETED =>'label label-danger',
        self::STATUS_ACTIVE =>'label label-success',
        self::STATUS_DISABLED =>'label label-warning',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_batch}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'draw_start_time', 'draw_end_time'], 'safe'],
            [['company_id', 'type', 'operator_id', 'draw_amount', 'amount', 'version','status'], 'integer'],
            [['draw_start_time', 'draw_end_time', 'operator_id'], 'required'],
            [['batch_no'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 1023],
            [['remark', 'operator_name'], 'string', 'max' => 255],
            ['status', 'default', 'value' => BonusBatch::STATUS_DISABLED],
            [[ 'draw_start_time', 'draw_end_time'], 'validateDateTime'],
            ['amount','compare','compareValue' => 0,'operator' =>'>',"message" => '预算金额必须大于0'],
        ];
    }


    public function validateDateTime($attribute, $params)
    {
        if (!StringUtils::isBlank($this->draw_start_time)&&!StringUtils::isBlank($this->draw_end_time)){
            if ($this->draw_end_time<=$this->draw_start_time){
                $this->addError('draw_start_time', '结束时间必须晚于开始时间');
                $this->addError('draw_end_time', '结束时间必须晚于开始时间');
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
            'company_id' => '公司ID',
            'batch_no' => '批次编号',
            'name' => '批次名称',
            'type' => '奖励金类型',
            'remark' => '备注',
            'draw_start_time' => '领取开始时间',
            'draw_end_time' => '领取结束时间',
            'operator_id' => '操作人ID',
            'operator_name' => '操作人姓名',
            'status' => '状态',
            'draw_amount' => '已发放金额',
            'amount' => '预算金额',
            'version' => '版本号',
        ];
    }
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord){
                $this->batch_no = UUIDUtils::uuidWithoutSeparator();
            }
            return true;
        } else {
            return false;
        }
    }

    public function restoreForm(){
        $this->amount = Common::showAmount($this->amount);
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
