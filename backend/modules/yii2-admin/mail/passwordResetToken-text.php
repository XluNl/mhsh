<?php
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user mdm\admin\models\User */

$resetLink = Url::to(['user/reset-password','token'=>$user->password_reset_token], true);
?>
你好  <?= $user->username ?>,

请点击下面的链接来重新设置您的密码

<?= $resetLink ?>
