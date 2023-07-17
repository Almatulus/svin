<?php

use core\helpers\HtmlHelper as Html;
use core\models\warehouse\Usage;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\UsageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Usage history');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label'    => $this->title,
    'url'      => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="sale-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id'           => 'usages',
        'dataProvider' => $dataProvider,
        'summary'      => Html::getSummary(),
        'columns'      => [
            [
                'class'           => '\yii\grid\CheckboxColumn',
                'checkboxOptions' => function (Usage $model, $key, $index, $column) {
                    return ['disabled' => !$model->isEnabled()];
                }
            ],
            [
                'format' => 'raw',
                'label'  => Yii::t('app', 'Name'),
                'value'  => function ($model) {
                    if (sizeof($model->usageProducts) > 1) {
                        $content = Html::beginTag('ol');
                        foreach ($model->usageProducts as $key => $usageItem) {
                            $title = $usageItem->product->name;
                            $content .= Html::beginTag('li')
                                . $title
                                . Html::endTag('li');
                        }
                        $content .= Html::endTag('ol');
                    } else {
                        $content = $model->usageProducts[0]->product->name;
                    }
                    return $content;
                }
            ],
            [
                'format' => 'html',
                'label'  => Yii::t('app', 'Quantity'),
                'value'  => function ($model) {
                    if (sizeof($model->usageProducts) > 1) {
                        $content = "";
                        foreach ($model->usageProducts as $key => $usageItem) {
                            $content .= $usageItem->quantity;
                            if (!($key == sizeof($model->usageProducts) - 1)) {
                                $content .= '<br>';
                            }
                        }
                    } else {
                        $content = $model->usageProducts[0]->quantity;
                    }
                    return $content;
                }
            ],
            [
                'format' => 'html',
                'label'  => Yii::t('app', 'Unit'),
                'value'  => function ($model) {
                    if (sizeof($model->usageProducts) > 1) {
                        $content = "";
                        foreach ($model->usageProducts as $key => $usageItem) {
                            $content .= $usageItem->product->unit->name;
                            if (!($key == sizeof($model->usageProducts) - 1)) {
                                $content .= '<br>';
                            }
                        }
                    } else {
                        $content = $model->usageProducts[0]->product->unit->name;
                    }
                    return $content;
                }
            ],
            [
                'attribute' => 'sum',
                'format'    => 'decimal'
            ],
            [
                'format'    => 'datetime',
                'attribute' => 'created_at',
            ],
            [
                'attribute' => 'staff.name',
                'label'     => Yii::t('app', 'Staff ID')
            ],
            [
                'attribute' => 'companyCustomer.customer.fullName',
                'label'     => Yii::t('app', 'Customer')
            ],
            'comments:text',
            [
                'class'          => 'yii\grid\ActionColumn',
                'buttons'        => [
                    'cancel' => function ($url, $model, $key) {
                        $title = Yii::t('app', 'Cancel');
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-remove"]);
                        return Html::a($icon, ['cancel', 'id' => $model->id], [
                            'title'        => $title,
                            'aria-label'   => $title,
                            'data-pjax'    => '0',
                            'data-confirm' => Yii::t('app', 'Are you sure you want to cancel this usage?'),
                            'data-method'  => 'post',
                        ]);
                    },
                ],
                'template'       => '{update} {cancel}',
                'visibleButtons' => [
                    'update' => function (Usage $model, $key, $index) {
                        return $model->isCanceled();
                    },
                    'cancel' => function (Usage $model, $key, $index) {
                        return $model->isEnabled();
                    },
                ],
            ]
        ],
    ]); ?>

</div>


<?php


$js = <<<JS
$(function () {
    var gridView = $('#usages');
    
    gridView.on('change', 'input:checkbox', function(e) {
        let keys = gridView.yiiGridView('getSelectedRows');
        if (keys.length > 0) {
            $('.js-cancel-usages').removeClass('disabled');
        } else {
            $('.js-cancel-usages').addClass('disabled');
        }
    });

    $('.js-cancel-usages').click(handleDeleteClick);
    
    function handleDeleteClick(e) {
        if (gridView.yiiGridView('getSelectedRows').length > 0) {
            if(confirm("Вы уверены что хотите отменить данные записи?") === true) {
                cancelUsages();
            }
        }
    }
    
    function cancelUsages() {
        let deleteURL = 'batch-cancel';
        let data = { 'selected': gridView.yiiGridView('getSelectedRows') };
        $.post(deleteURL, data, function(response) {
            let message = "<b>Отменено: " + response.deleted + '</b><br><br>';
            message += response.errors.map(function(msg) { return '<p class="red">' + msg + '</p>'}).join('');
            alertMessage(message, function() {
                location.reload();
            });
        });
    }
});
JS;

$this->registerJs($js);