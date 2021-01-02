<?php

use backend\models\BackendCommon;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\CompanyInviteCode */

$this->title = "邀请码";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-invite-code-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('生成团长邀请注册码', ['company-invite-code/refresh-business' ], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('生成商户邀请注册码', ['company-invite-code/refresh-alliance' ], ['class' => 'btn btn-info']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'business_invite_image',
                'format' => [
                    'image',
                    [
                        'onerror' => 'ifImgNotExists(this)',
                        'class' => 'img-thumbnail',
                        'width'=>'430',
                        'height'=>'430'
                    ]
                ],
                'value' => function ($model) {
                    return $model->business_invite_image;
                },
            ],
            [
                'attribute' => 'alliance_invite_image',
                'format' => [
                    'image',
                    [
                        'onerror' => 'ifImgNotExists(this)',
                        'class' => 'img-thumbnail',
                        'width'=>'430',
                        'height'=>'430'
                    ]
                ],
                'value' => function ($model) {
                    return $model->alliance_invite_image;
                },
            ],
            [
//                'attribute' => 'user_invite_image',
                'format' => [
                    'image',
                    [
                        'onerror' => 'ifImgNotExists(this)',
                        'class' => 'img-thumbnail',
                        'width'=>'430',
                        'height'=>'430'
                    ]
                ],
                'label' => '用户注册码',
                'value' => function ($model) {
                    return BackendCommon::generateAbsoluteUrl("/userMini.jpg");
                },
            ],
        ],
    ]) ?>

</div>
