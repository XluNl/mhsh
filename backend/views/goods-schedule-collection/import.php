<?php


/* @var $this yii\web\View */
/* @var $model backend\models\forms\GoodsScheduleImportForm */
/* @var $goodsScheduleCollectionModel GoodsScheduleCollection */

use common\models\GoodsScheduleCollection;
use yii\bootstrap\Html;
use backend\assets\BootstrapTreeviewAsset;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;

BootstrapTreeviewAsset::register($this);
$title = "排期导入";
$this->params['subtitle'] = $title;
$this->params['breadcrumbs'][] = ['label' => '商品排期列表', 'url' => ['/goods-schedule-collection/index','GoodsScheduleSearch[collection_id]'=>$goodsScheduleCollectionModel['id']]];
$this->params['breadcrumbs'][] = $title;
?>
<div class="container-fluid">
<div class="row">
	<div class="col-xs-6 col-xs-offset-3">
	<div class="box box-success box-solid">
	       <div class="box-header with-border">
               给(<?=$goodsScheduleCollectionModel['collection_name']?>)导入商品排期
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
                           'columns'=>12,
                           'autoGenerateColumns'=>false, // override columns setting
                           'attributes'=>[       // 3 column layout
                               'collection_id' => ['type' => Form::INPUT_HIDDEN],
                               'file'=>[
                                   'label'=> '导入文件',
                                   'type'=>Form::INPUT_WIDGET,
                                   'widgetClass'=>'\kartik\widgets\FileInput',
                                   'options'=>[
                                       'options' => ['multiple' => false,'accept' => 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                                       'language'=>'zh-CN',
                                       'pluginOptions' => [
                                           'showCaption' => true,
                                           'showRemove' => true,
                                           'showUpload' => false,
                                           'showPreview' => false,
                                           'allowedFileExtensions' => ['xls', 'xlsx'],
                                           'browseLabel' => '选择excel文件',
                                           'msgFilesTooMany' => "选择上传的文件数量({n}) 超过允许的最大文件数{m}！",
                                           'maxFileCount' => 1,//允许上传最多的图片1张
                                           'maxFileSize' => 4096000,//限制图片最大200kB
                                       ],
                                   ],
                                   'columnOptions'=>['colspan'=>10]
                               ],
                           ]
                       ],
                   ]
               ]); ?>
        	    <div class="form-group">
        	        <?= Html::submitButton('上传', ['class' => 'col-xs-offset-2 col-xs-3 btn   btn-primary btn-lg ']) ?>
            	    <?= Html::a('返回', ['/goods-schedule/index','GoodsScheduleSearch[collection_id]'=>$model['collection_id']], ['class' => 'col-xs-offset-2 col-xs-3 btn   btn-warning btn-lg']) ?>
        	    </div>
        	<?php ActiveForm::end(); ?>
        </div>
    </div>
	</div>
</div>
	
</div>