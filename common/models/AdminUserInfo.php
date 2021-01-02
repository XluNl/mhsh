<?php

namespace common\models;

/**
 * This is the model class for table "{{%admin_user_info}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $pic
 * @property string $phone
 * @property integer $sex
 * @property string $mark
 * @property string $nickname
 */
class AdminUserInfo extends \yii\db\ActiveRecord
{

    public $nickname;

    public static $sexArr =[
        '1'=>'男',
        '2'=>'女',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'sex'], 'integer'],
            [['pic'], 'string'],
            [['mark','nickname'], 'string','length' => [0, 50]],
            [['phone'],'match','pattern'=>'/^([1][3758][0-9]{9}|[0-9]{7,8}|400[0-9]{7}|800[0-9]{7}|[0-9]{3,4}-[0-9]{7,8})$/','message'=>'手机号或者座机格式不正确（座机格式为88888888或者0555-23232323）'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'pic' => '头像',
            'phone' => '电话',
            'sex' => '性别',
            'mark' => '个性签名',
            'nickname'=>'昵称',
        ];
    }
}
