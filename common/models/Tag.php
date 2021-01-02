<?php

namespace common\models;

use common\utils\ArrayUtils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "sptx_tag".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $company_id
 * @property integer $group_id
 * @property integer $tag_id
 * @property string $biz_id
 * @property string $biz_name
 * @property string $biz_ext
 * @property string $start_time
 * @property string $end_time
 */
class Tag extends \yii\db\ActiveRecord
{


    const START_TIME = "2000-01-01 00:00:00";
    const END_TIME = "2099-01-01 00:00:00";

    const GROUP_DELIVERY =  1;

    public static $groupArr = [
        self::GROUP_DELIVERY=>'团长标签',
    ];

    public static $groupMap = [
        self::GROUP_DELIVERY=>[
            self::TAG_DELIVERY_PLATFORM_ROYALTY,
        ],
    ];

    public static function getGroupTagArr($groupId){
        if (key_exists($groupId,self::$groupMap)){
            $arr = [];
            foreach (self::$groupMap[$groupId] as $v){
                $arr[$v] = ArrayUtils::getArrayValue($v,self::$tagArr);
            }
            return $arr;
        }
        else{
            return [];
        }

    }

    public static  $groupCssArr=[
        self::GROUP_DELIVERY=>'label label-success',
    ];

    const TAG_DELIVERY_PLATFORM_ROYALTY = 1;

    public static $tagArr = [
        self::TAG_DELIVERY_PLATFORM_ROYALTY=>'平台提成',
    ];

    public static $tagDefaultArr = [
        //平台提成默认值
        self::TAG_DELIVERY_PLATFORM_ROYALTY=> 240,
    ];

    public static $tagCssArr = [
        self::TAG_DELIVERY_PLATFORM_ROYALTY=>'label label-success',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sptx_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'start_time', 'end_time'], 'safe'],
            [['company_id', 'group_id', 'tag_id'], 'integer'],
            [['group_id', 'tag_id', 'biz_id', 'start_time', 'end_time'], 'required'],
            [['biz_id', 'biz_name', 'biz_ext'], 'string', 'max' => 255],
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
            'company_id' => 'Company ID',
            'group_id' => '组id',
            'tag_id' => '标签名称',
            'biz_id' => '业务id',
            'biz_name' => '业务名称',
            'biz_ext' => '业务ext',
            'start_time' => '有效期开始时间',
            'end_time' => '有效期截止时间',
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
}
