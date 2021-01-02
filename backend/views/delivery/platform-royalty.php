<?php


/* @var $this yii\web\View */
/* @var $model backend\models\forms\DeliveryTagForm
 */

use yii\bootstrap\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;

$title = "修改平台提成比例";
$this->params['subtitle'] = $title;
$this->params['breadcrumbs'][] = ['label' => '团长列表', 'url' => ['/delivery/index']];
$this->params['breadcrumbs'][] = $title;
?>
<div class="container-fluid">
<div class="row">
	<div class="col-xs-6 col-xs-offset-3">
	<div class="box box-success box-solid">
	       <div class="box-header with-border">
               修改社区合伙人（<?= $model['delivery']['nickname']?>）的平台提成比例
           </div>
           <div class="box-body">
    		<?php $form = ActiveForm::begin([
                'options'=>['enctype'=>'multipart/form-data',
                'type'=>ActiveForm::TYPE_VERTICAL],
            ]); ?>
               <?php echo FormGrid::widget([
                   'model'=>$model,
                   'form'=>$form,
                   'autoGenerateColumns'=>true,
                   'rowOptions'=>['class'=>'col-md-offset-1'],
                   'rows'=>[
                       [
                           'attributes' => [       // 3 column layout
                               'delivery_id' => ['type' => Form::INPUT_HIDDEN],
                               'tag_info_id' => ['type' => Form::INPUT_HIDDEN],
                           ]
                       ],
                       [
                           'columns'=>12,
                           'autoGenerateColumns'=>false, // override columns setting
                           'attributes'=>[       // 3 column layout
                               'tag_name' => ['type' => Form::INPUT_TEXT,  'options' => ['readonly' => 'readonly'],'columnOptions' => ['colspan' => 4]],
                               'tag_value' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入平台提成比例(百分比)...'], 'columnOptions' => ['colspan' => 4]],
                           ]
                       ],
                   ]
               ]); ?>
        	    <div class="form-group">
        	        <?= Html::submitButton('保存', ['class' => 'col-xs-offset-2 col-xs-3 btn   btn-primary btn-lg ']) ?>
            	    <?= Html::a('返回', ['/delivery/index', 'DeliverySearch[id]' => $model['delivery_id']], ['class' => 'col-xs-offset-2 col-xs-3 btn   btn-warning btn-lg']) ?>
        	    </div>
        	<?php ActiveForm::end(); ?>
        </div>
    </div>
	</div>
</div>
</div>