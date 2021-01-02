<?php

namespace common\models;
use yii\data\ActiveDataProvider;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%group_active_rules}}".
 *
 * @property string $id
 * @property string $main_id
 * @property string $goods_id
 * @property string $attr_id
 * @property string $name
 * @property string $rules_desc
 * @property string $created_at
 * @property string $updated_at
 */
class GroupActiveRules extends \yii\db\ActiveRecord
{
    public $sku;
    public $rules;
    public $maxLevel;
    // public $price1;
    // public $price2;
    // public $price3;
    // public $price4;
    // public $price5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%group_active_rules}}';
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
            [['active_id', 'goods_id', 'sku_id', 'rule_desc','sku_stock'], 'required'],
            [['active_id', 'goods_id', 'sku_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['rule_desc'], 'string', 'max' => 225],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'active_id' => '关联主表id',
            'goods_id' => '商品id',
            'sku_id' => '规格id',
            'sku_stock'  => '库存',
            'sku_name' => '规则名称',
            'rule_desc' => '规则内容',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function add($data){     
        if($this->load($data) && $this->save()){
           return true;
        }
        return false;
    }

    public function search($params)
    {
        $group_active = GroupActiveRules::tableName();
        $query = GroupActiveRules::find();

        $query->andFilterWhere([
            "main_id" => $this->main_id,
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        // $query->orderBy('id desc');
        return $dataProvider;
    }

    public function desc($params=[]){
       if(!empty($params)&& is_array($params)){
         return $params;
       }
       $rules = json_decode($this->rule_desc,true);
       $maxLevel = 0;
       foreach ($rules as $key => $value) {
           if(!empty($value)){
             $maxLevel+=1;
           }
       }
       $this->maxLevel = $maxLevel;
       $this->rules = $rules;
    }

    public function getGoodsSku(){
        return $this->hasOne(GoodsSku::className(),['id'=>'sku_id','goods_id'=>'goods_id'])->select('id,goods_id,sku_name,sku_img,sku_unit,sku_standard,sku_unit_factor,sku_describe,sku_status,sku_stock,sku_sold,sale_price,purchase_price,reference_price,start_sale_num');
    }

    public function getGroupActive(){
        return $this->hasOne(GroupActive::className(),['id'=>'active_id']);
    }

    public function getGoodsScheduleInfo(){
        return $this->hasOne(GoodsSchedule::className(),['sku_id'=>'sku_id'])->select('id,sku_id,price');
    }
}
