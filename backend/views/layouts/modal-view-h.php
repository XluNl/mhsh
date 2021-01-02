<?php

use backend\models\constants\ModalShowConstants;
use backend\models\ModelViewUtils;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\web\View;

/* @var string $modelType */
/* @var string $modalId */
/* @var string $title */
/* @var array $columns */
/* @var string $requestUrl */
Modal::begin([
    'id' => $modalId,
    'header' => '<h4 class="modal-title">'.$title.'</h4>',
    'footer' => '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button><button type="button" class="btn btn-primary" onclick="'.$modalId.'Submit();">提交</button>',
]);


echo $this->render($modelType, [
    'modalId' => $modalId,
    'columns'=>$columns,
]);

?>

<?php
Modal::end();
?>

<?php $this->beginBlock("{$modalId}_js_end");?>
    $("."+"<?=$modalId?>").click(function(){
        <?php
            foreach ($columns as $col){
                echo ModelViewUtils::setValueHtml($col['type'],$modalId,$col['key']);
            }
        ?>
        $("#<?=$modalId?>").modal("show");
        return false;
    });

    function <?=$modalId?>Submit(obj){
        let data = $("#<?=$modalId?>-form").serializeArray();
        let url = "<?=$requestUrl?>";
        $(obj).button('loading');
        $.post(url, data,function(data,status){
            let message  = "";
            if (data===undefined){
                message = "网络错误";
            }
            else if (data.status===false){
                message = (data.error);
            }
            else {
                message = (data.data);
            }
            bootbox.alert({
                message: message,
                callback: function () {
                    window.location.reload();
                }
            });
            $(obj).button('reset');

        },"json");
    }

<?php
$this->endBlock();
$this->registerJs($this->blocks["{$modalId}_js_end"],View::POS_END);

