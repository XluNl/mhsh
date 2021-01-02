<?php
/* @var string $modalId */
/* @var array $columns */

use backend\models\ModelViewUtils;
use yii\helpers\Html; ?>
<style>
    .modal-dialog .modal-content .modal-body table input,.modal-dialog .modal-content .modal-body table textarea{
        width: 100%;
    }
</style>
<?= Html::beginForm('','',['id'=>"{$modalId}-form"]);?>
    <div class="table-responsive">
        <table class="table table-th-block table-primary">
            <thead>
                <?php foreach ($columns as $col):?>
                    <?php if (!ModelViewUtils::isHiddenRow($col['type'])):?>
                        <th><?= $col['title'] ?></th>
                    <?php endif;?>
                <?php endforeach;?>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($columns as $col):?>
                    <?php if (!ModelViewUtils::isHiddenRow($col['type'])):?>
                        <td>
                            <?php echo $col['content']?>
                        </td>
                    <?php else:?>
                        <?php echo $col['content']?>
                    <?php endif;?>

                <?php endforeach;?>
            </tr>
            </tbody>
        </table>
    </div>
<?= Html::endForm();?>