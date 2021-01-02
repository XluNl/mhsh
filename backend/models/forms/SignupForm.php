<?php
namespace backend\models\forms;

use backend\models\BackendCommon;
use mdm\admin\components\UserStatus;
use mdm\admin\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $retypePassword;
    public $nickname;
    public $company_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => $class, 'message' => '用户名已存在'],
            ['username', 'string', 'min' => 2, 'max' => 24],

            ['nickname', 'required'],
            ['nickname', 'string','min' => 1, 'max' => 24],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => $class, 'message' => 'Email已存在'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['retypePassword', 'required'],
            ['retypePassword', 'compare', 'compareAttribute' => 'password'],

            ['retypePassword', 'required','on'=>['signup']],
            ['retypePassword', 'compare', 'compareAttribute'=>'password', 'message'=>'两次密码输入不一致'],

            [['username', 'password','retypePassword'],'match','pattern'=>'/^\w+$/','message'=>'只允许输入字母、数字和下划线'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'nickname' => '昵称',
            'email' => '电子邮箱',
            'password' => '密码',
            'retypePassword' => '确认密码',
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';
            $user = new $class();
            $user->username = $this->username;
            $user->nickname = $this->nickname;
            $user->email = $this->email;
            $user->status = ArrayHelper::getValue(Yii::$app->params, 'user.defaultStatus', UserStatus::ACTIVE);
            if (empty($this->company_id)) {
                $user->company_id = BackendCommon::getFCompanyId();
            }
            else {
                $user->company_id = $this->company_id;
            }
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }
}
