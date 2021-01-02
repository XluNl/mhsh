<?php

namespace common\models;

/**
 * This is the model class for table "{{%sg_goods}}".
 *
 * @property string $id
 * @property string $goods_name
 * @property string $sort_parent
 * @property string $sort_parent_name
 * @property string $sort_child
 * @property string $sort_child_name
 * @property string $goods_description
 * @property string $goods_addtime
 * @property string $goods_uptime
 * @property integer $user_id
 * @property integer $status;
 */
class Sggoods extends \yii\db\ActiveRecord
{
   
    public static $status_list=array(
        0=>'未审核',
        1=>'审核通过',
        2=>'审核拒绝',
    );
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sg_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['goods_name', 'goods_addtime'], 'string', 'max' => 20],
            [['goods_description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sort_parent' => '父类ID',
            'sort_parent_name' => '父类名称',
            'sort_child' => '子类ID',
            'sort_child_name' => '子类名称',
            'goods_name' => '建议商品的名称',
            'goods_description' => '建议商品的描述',
            'goods_addtime' => '添加时间',
            'goods_addtime' => '更新时间',
            'user_id' => '商户ID',
            'status' => '状态',
        ];
    }
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->goods_addtime = time();
                $this->status = 0;
            }
            $this->goods_uptime = time();
            return true;
        } else {
            return false;
        }
    
    }
}
