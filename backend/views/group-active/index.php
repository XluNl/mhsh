<?php

use backend\models\BackendCommon;
use backend\utils\BackendViewUtil;
use common\models\GroupActive;
use frontend\services\GroupActiveService;
use kartik\grid\GridView;
use kartik\popover\PopoverX;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $deliveryNames array
 * @var $allianceNames array
 */
$this->title = '拼团活动列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body th {
        text-align: center;
    }

    .field-groupactivesearch-status {
        display: flex;
        flex-direction: column;
    }
</style>


<div class="container-fluid">

    <div class="">
        <p style="">
            <?= Html::a('新增', 'modify', ['class' => 'btn btn-primary']) ?>
        </p>
    </div>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="panel with-nav-tabs panel-primary" style="text-align: center">
        <?php echo $this->render('filter', ['owner_type' => $searchModel->owner_type]); ?>
        <div class="box-body" style="text-align: center">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'caption' => "会员列表",
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'active_no',
                    [
                        'label' => '商品id',
                        'value' => function ($model) {
                            return $model['schedule']['goods']['id'];
                        },
                    ],
                    [
                        'label' => '商品名称',
                        'value' => function ($model) {
                            return $model['schedule']['goods']['goods_name'];
                        },
                    ],
                    [
                        'attribute' => 'continued',
                        'label' => '活动持续时间(分钟)'
                    ],
                    [
                        'label' => '基础价格',
                        'value' => function ($model) {
                            return BackendCommon::showAmountWithYuan($model['schedule']['price']);
                        },
                    ],
                    [
                        'label' => '团购价格',
                        'format' => 'raw',
                        'value' => function ($model) {
                            list($ruleDesc,$maxLevel) = GroupActiveService::decodeRules($model['rule_desc']);
                            $str = "";
                            foreach ($ruleDesc as $item){
                                $str.=$item['text'].':'.BackendCommon::showAmountWithYuan($item['price']).'<br/>';
                            }
                            return PopoverX::widget([
                                'header' => '价格明细',
                                'placement' => PopoverX::ALIGN_RIGHT,
                                'content' => $str,
                                'footer' => '',
                                'toggleButton' => [
                                    'label' => '价格明细',
                                    'class' => 'btn btn-info btn-xs',
                                ],
                            ]);
                        },
                    ],
                    [
                        // 'attribute' => 'continued',
                        'label' => '活动时间段',
                        'value' => function ($model) {
                            return $model['schedule']['online_time'] . '-' . $model['schedule']['offline_time'];
                        }
                    ],
                    'operator_name',
                    'created_at',
                    [
                        'attribute' => 'status',
                        'label' => '活动状态',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return BackendViewUtil::getArrayWithLabel($model['status'], GroupActive::$statusArr, GroupActive::$statusCssArr);
                        },
                    ],
                    [
                        'header' => '操作',
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{edit}{view}{up}{down}{delete}{room}',
                        'headerOptions' => ['width' => '260'],
                        'buttons' => [
                            'edit' => function ($url, $model, $key) {
                                return Html::a('编辑', Url::toRoute(['modify', 'active_id' => $model->id]), ['class' => 'btn btn-primary btn-xs']);
                            },
                            'view' => function ($url, $model, $key) {
                                return Html::a('详情', 'javascript:void(0);', ['value' => $model->id, 'class' => 'btn btn-info btn-xs group_detail']);
                            },
                            'delete' => function ($url, $model, $key) {
                                return BackendViewUtil::generateOperationATag("删除", ['operation', 'active_id' => $model->id, 'commander' => GroupActive::STATUS_DELETED], 'btn btn-xs btn-danger', 'fa fa-trash', "确认删除？");
                            },
                            'up' => function ($url, $model, $key) {
                                if ($model->status == GroupActive::STATUS_UP) {
                                    return "";
                                }
                                return BackendViewUtil::generateOperationATag("上线", ['operation', 'active_id' => $model->id, 'commander' => GroupActive::STATUS_UP], 'btn btn-xs btn-success', 'fa fa-cloud-upload', "确认上线？");
                            },
                            'down' => function ($url, $model, $key) {
                                if ($model->status == GroupActive::STATUS_DOWN) {
                                    return "";
                                }
                                return BackendViewUtil::generateOperationATag("下线", ['operation', 'active_id' => $model->id, 'commander' => GroupActive::STATUS_DOWN], 'btn btn-xs btn-warning', 'fa fa-cloud-download', "确认下线？");
                            },
                            'room' => function ( $url, $model, $key) {
                                return BackendViewUtil::generateOperationATag("房间",['/group-room/index','GroupRoomSearch[active_no]'=>$model['active_no']],'btn btn-xs btn-primary','fa fa-envelope',null,['target'=>'_blank']);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<script type="text/javascript">

    $('.group_detail').on('click', function () {
        var active_id = $(this).attr('value');
        layer.open({
            type: 2,
            area: ['70%', '80%'],
            fixed: false,
            title: '详情',
            maxmin: true,
            content: ["<?= Url::toRoute(['detail']);?>" + '?active_id=' + active_id]
        });
    });
    //删除
    $('.group_del').on('click', function () {
        let active_id = $(this).attr('value');
        item = $(this).parent().parent();
        let con_index = layer.confirm('是否确定删除!', {
                btn: ['删除', '取消'],
                title: '删除',
            },
            function () {
                m_index = layer.load();
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: "<?= Url::toRoute(['del']);?>",
                    data: {
                        'active_id': active_id,
                        '_csrf': '<?php echo Yii::$app->request->csrfToken ?>'
                    },
                    success: function (re) {
                        if (re.status) {
                            layer.msg('删除成功!', {icon: 1});
                            item.fadeOut(2000, function () {
                                $(this).remove();
                            });
                        } else {
                            layer.msg(re.error, {icon: 2});
                        }
                    }, error: function (re) {
                        layer.msg('删除失败!', {icon: 2});
                    }, complete: function () {
                        layer.close(m_index);
                    }
                });
            }, function () {
            }
        );
    });
</script>