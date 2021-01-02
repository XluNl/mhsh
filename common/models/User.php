<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $openid
 * @property string $unionid
 * @property string $headimgurl
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $salt
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $access_token
 * @property string $password write-only password
 * @property integer $user_type
 * @property integer $user_info_id
 * @property integer $delivery_id
 * @property string $create_ip
 * @property integer $sex   //性别 0：未知、1：男、2：女
 * @property string $nickname
 * @property string $last_login
 */
class User extends ActiveRecord implements IdentityInterface {

	public $repassword = "";
	public $phone;
	public $order_count;
	public $amount_sum;

	const STATUS_DELETED = 0;
	const STATUS_ACTIVE = 1;

	const USER_TYPE_CUSTOMER = 1;
	const USER_TYPE_BUSINESS = 2;
    const USER_TYPE_ALLIANCE = 3;
    const USER_TYPE_OFFICIAL = 4;

	public $userTypeArr =[
	    self::USER_TYPE_CUSTOMER=>'用户端',
        self::USER_TYPE_BUSINESS=>'商务端',
        self::USER_TYPE_ALLIANCE=>'联盟端',
        self::USER_TYPE_OFFICIAL=>'微信公众号端',
    ];
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%user}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [
			TimestampBehavior::className(),
		];
	}

	public function beforeSave($insert) {
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->create_ip = Yii::$app->request->userIP;
			}
			return true;
		} else {
			return false;
		}
	}

	public function attributeLabels() {
		return [
			'username' => '用户名',
			'password' => '用户密码',
			'repassword' => '重复密码',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
			array(array("openid", "username"), "required", "on" => "create"),
            ['sex', 'integer'],
            ['nickname', 'string','max' => 255],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id) {
		return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null) {
        return static::findOne(['access_token' => $token]);
	}


    # 生成access_token
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
    }


	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @return static|null
	 */
	public static function findByUsername($username) {
		return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
	}

	public static function findByMobile($mobile) {
		return static::findOne(array("mobile" => $mobile, 'status' => self::STATUS_ACTIVE));
	}

	public static function findByEmail($email) {
		return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 * @return static|null
	 */
	public static function findByPasswordResetToken($token) {
		if (!static::isPasswordResetTokenValid($token)) {
			return null;
		}

		return static::findOne([
			'password_reset_token' => $token,
			'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds out if password reset token is valid
	 *
	 * @param string $token password reset token
	 * @return boolean
	 */
	public static function isPasswordResetTokenValid($token) {
		if (empty($token)) {
			return false;
		}
		$timestamp = (int) substr($token, strrpos($token, '_') + 1);
		$expire = Yii::$app->params['user.passwordResetTokenExpire'];
		return $timestamp + $expire >= time();
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->getPrimaryKey();
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthKey() {
		return $this->auth_key;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey) {
		return $this->getAuthKey() === $authKey;
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return boolean if password provided is valid for current user
	 */
	public function validatePassword($password) {
		return Yii::$app->security->validatePassword($password . "" . $this->salt, $this->password);
		/*if ($this->password == md5($password . "" . $this->salt)) {
				return true;
			}
		*/
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 */
	public function setPassword($password) {
		//$this->password = Yii::$app->security->generatePasswordHash($password);
		$this->password = md5($password . "" . $this->salt);
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey() {
		$this->auth_key = Yii::$app->security->generateRandomString();
	}

	/**
	 * Generates new password reset token
	 */
	public function generatePasswordResetToken() {
		$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken() {
		$this->password_reset_token = null;
	}
}
