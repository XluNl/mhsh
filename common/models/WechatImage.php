<?php

namespace common\models;

/**
 * This is the model class for table "{{%wechat_image}}".
 *
 * @property string $id
 * @property string $url
 * @property string $module
 * @property integer $company_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class WechatImage extends \yii\db\ActiveRecord
{
    const MODULE_SLIDER = 'slider';
    const MODULE_BLANK_IMAGE = 'blank_image';
    public static $moduleArr=[
        'slider',
        'blank_image'
    ];
    public static $moduleMaxCount=[
        'slider'=>10,
        'blank_image'=>1,
    ];
    public static $moduleNames=[
        'slider'=>'滚动横幅',
        'blank_image'=>'空白图片',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'created_at', 'updated_at'], 'integer'],
            [['url', 'module'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => '图像ID',
            'module' => '分类',
            'company_id' => '公司ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}
