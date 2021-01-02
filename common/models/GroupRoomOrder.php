<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%group_room_order}}".
 *
 * @property int $id id
 * @property string $active_no 团购活动no
 * @property string $room_no 拼团房间no
 * @property int $customer_id 用户id
 * @property string $order_no 订单编号
 * @property int|null $schedule_amount 下单价格
 * @property int|null $active_amount 最终拼团价格
 * @property int $company_id 公司id
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class GroupRoomOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%group_room_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active_no', 'room_no', 'customer_id', 'order_no', 'company_id'], 'required'],
            [['customer_id', 'schedule_amount', 'active_amount', 'company_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['active_no', 'room_no'], 'string', 'max' => 50],
            [['order_no'], 'string', 'max' => 64],
            //[['customer_id'], 'unique', 'targetAttribute' => ['customer_id', 'room_no'], 'message' => '同一个团只能参加一次'],
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
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'active_no' => '团购活动编号',
            'room_no' => '拼团房间编号',
            'customer_id' => '用户id',
            'order_no' => '订单编号',
            'schedule_amount' => '下单价格',
            'active_amount' => '最终拼团价格',
            'company_id' => '公司id',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    public function getOrder(){
        return $this->hasOne(Order::className(),['order_no'=>'order_no']);
    }

   /* public function getSimpleOrder(){
        return $this->hasOne(Order::className(),['order_no'=>'order_no'])
            ->select("");
    }*/

    public function getGroupRoom(){
        return $this->hasOne(GroupRoom::className(),['room_no'=>'room_no']);
    }

    public function getGroupActive(){
        return $this->hasOne(GroupActive::className(),['active_no'=>'active_no']);
    }

}
