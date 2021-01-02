<?php

namespace backend\models\forms;

use mdm\admin\components\UserStatus;
use mdm\admin\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $password;
    public $retypePassword;
    /**
     * @var User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param  string $token
     * @param  array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('密码重置令牌不能为空.');
        }
        // check token
        $class = Yii::$app->getUser()->identityClass ?: 'mdm\admin\models\User';
        if (static::isPasswordResetTokenValid($token)) {
            $this->_user = $class::findOne([
                    'password_reset_token' => $token,
                    'status' => UserStatus::ACTIVE
            ]);
        }
        if (!$this->_user) {
            throw new InvalidParamException('错误的密码重置令牌.');
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'retypePassword'], 'required'],
            ['password', 'string', 'min' => 6],
            ['retypePassword', 'compare', 'compareAttribute' => 'password'],
            [['password'],'match','pattern'=>'/^\w+$/','message'=>'只允许输入字母、数字和下划线']
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => '新密码',
            'retypePassword' => '重复密码',
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = ArrayHelper::getValue(Yii::$app->params, 'user.passwordResetTokenExpire', 24 * 3600);
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }
}
