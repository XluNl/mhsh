<?php
/* @var string $modalId */

use backend\models\ModelViewUtils;
use yii\helpers\Html;

/* @var array $columns */
?>
<?= Html::beginForm('','',['id'=>"{$modalId}-form"]);?>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1">
            <div class="box ">
                <div class="box-body">
                    <div class="form-horizontal">
                        <?php foreach ($columns as $col):?>
                        <div class="form-group">
                            <?php if (!ModelViewUtils::isHiddenRow($col['type'])):?>
                                <?= Html::label(
                                        $col['title'],
                                        ModelViewUtils::getAttrId($modalId,$col['key']),
                                        ['class'=>"control-label col-lg-2"]
                                ) ?>
                            <?php endif;?>
                            <div class="col-lg-9">
                                <?php echo $col['content']?>
                            </div>
                        </div>
                        <?php endforeach;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= Html::endForm();?>