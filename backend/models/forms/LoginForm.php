<?php

namespace backend\models\forms;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = false;
    public $verifyCode;
    
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['verifyCode', 'captcha','captchaAction'=>'/admin/user/captcha'],
            [[ 'password'], 'string', 'length' => [6, 24]],
            [['username'], 'string', 'length' => [1, 24]],
            [['username', 'password'],'match','pattern'=>'/^\w+$/','message'=>'只允许输入字母、数字和下划线']
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '用户名或密码不正确');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->getUser()->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 7: 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';
            $this->_user = $class::findByUsername($this->username);
        }

        return $this->_user;
    }
    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'verifyCode'=>'验证码',
        ];
    }
}
