<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%api_log}}".
 *
 * @property int $id
 * @property string|null $ip
 * @property string|null $app_id
 * @property string|null $module
 * @property string|null $controller
 * @property string|null $action
 * @property string|null $request
 * @property string|null $response
 * @property int|null $created_at
 */
class ApiLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%api_log}}';
    }

    const TYPE_DEV = 1;
    const TYPE_TEST = 2;
    const TYPE_PROD = 3;
    public static $typeArr = [
        self::TYPE_DEV=>'dev',
        self::TYPE_TEST=>'test',
        self::TYPE_PROD=>'prod'
    ];
    public static $typeArrText = [
        self::TYPE_DEV=>'开发',
        self::TYPE_TEST=>'测试',
        self::TYPE_PROD=>'生产'
    ];

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value' => time(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request', 'response'], 'string'],
            [['created_at'], 'integer'],
            [['ip', 'app_id', 'module','env'], 'string', 'max' => 64],
            [['controller', 'action'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'app_id' => 'App ID',
            'module' => 'Module',
            'controller' => 'Cont',
            'action' => 'Action',
            'request' => 'Request',
            'response' => 'Response',
            'env' => 'Env',
            'created_at' => 'Created At',
        ];
    }

    public function add($data){
        $l_d['ApiLog'] =$data;
        if($this->load($l_d) && $this->save()){
            return true;
        }
        return false;
    }
}
