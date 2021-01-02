<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user mdm\admin\models\User */

$resetLink = Url::to(['user/reset-password','token'=>$user->password_reset_token], true);
?>
<div class="password-reset">
    <p>你好 <?= Html::encode($user->username) ?>,</p>

    <p>请点击下面的链接来重新设置您的密码</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
