<?php

use core\helpers\HtmlHelper as Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\SaleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Sales');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label' => $this->title,
    'url' => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="sale-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

        <?= GridView::widget([
        'id'           => 'sales',
        'dataProvider' => $dataProvider,
        'summary'      => Html::getSummary(),
        'columns'      => [
            ['class' => 'yii\grid\CheckboxColumn'],
                [
                    'format' => 'raw',
                    'label' => Yii::t('app', 'Name'),
                    'value' => function($model) {
                        if (sizeof($model->saleProducts) > 1) {
                            $content = Html::beginTag('ol');
                            foreach ($model->saleProducts as $key => $saleItem) {
                                $title = $saleItem->product->name;
                                $content .= Html::beginTag('li')
                                        . $title
                                        . Html::endTag('li');
                            }
                            $content .= Html::endTag('ol');
                        } else {
                            $content = $model->saleProducts[0]->product->name;
                        }
                        return $content;
                    }
                ],
                [
                    'format' => 'html',
                    'label'  => Yii::t('app', 'Price'),
                    'value'  => function ($model) {
                        if (sizeof($model->saleProducts) > 1) {
                            $content = "";
                            foreach ($model->saleProducts as $key => $saleItem) {
                                $content .= $saleItem->price;
                                if (!($key == sizeof($model->saleProducts) - 1)) {
                                    $content .= '<br>';
                                }
                            }
                        } else {
                            $content = $model->saleProducts[0]->price;
                        }
                        return $content;
                    }
                ],
                [
                    'format' => 'html',
                    'label'  => Yii::t('app', 'Quantity'),
                    'value'  => function($model) {
                        if (sizeof($model->saleProducts) > 1) {
                            $content = "";
                            foreach ($model->saleProducts as $key => $saleItem) {
                                $content .= $saleItem->quantity;
                                if (!($key == sizeof($model->saleProducts) - 1)) {
                                    $content .= '<br>';
                                }
                            }
                        } else {
                            $content = $model->saleProducts[0]->quantity;
                        }
                        return $content;
                    }
                ],
                [
                    'format' => 'html',
                    'label'  => Yii::t('app', 'Discount'),
                    'value'  => function ($model) {
                        if (sizeof($model->saleProducts) > 1) {
                            $content = "";
                            foreach ($model->saleProducts as $key => $saleItem) {
                                $content .= $saleItem->discount;
                                if (!($key == sizeof($model->saleProducts) - 1)) {
                                    $content .= '<br>';
                                }
                            }
                        } else {
                            $content = $model->saleProducts[0]->discount;
                        }
                        return $content;
                    }
                ],
                [
                    'format' => 'html',
                    'label'  => Yii::t('app', 'Unit'),
                    'value'  => function($model) {
                        if (sizeof($model->saleProducts) > 1) {
                            $content = "";
                            foreach ($model->saleProducts as $key => $saleItem) {
                                $content .= $saleItem->product->unit->name;
                                if (!($key == sizeof($model->saleProducts) - 1)) {
                                    $content .= '<br>';
                                }
                            }
                        } else {
                            $content = $model->saleProducts[0]->product->unit->name;
                        }
                        return $content;
                    }
                ],
                [
                    'attribute' => 'totalCost',
                    'label' => Yii::t('app', 'Total sum'),
                    'format' => 'decimal'
                ],
                [
                    'format'    => 'datetime',
                    'attribute' => 'sale_date',
                ],
                [
                    'attribute' => 'staff.name',
                    'label' => Yii::t('app', 'Staff ID')
                ],
                [
                    'attribute' => 'companyCustomer.customer.fullName',
                    'label' => Yii::t('app', 'Customer')
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}'
                ]
            ],
        ]); ?>
</div>

<?php


$js = <<<JS
$(function () {
    var gridView = $('#sales');
    
    gridView.on('change', 'input:checkbox', function(e) {
        let keys = gridView.yiiGridView('getSelectedRows');
        if (keys.length > 0) {
            $('.js-delete-sales').removeClass('disabled');
        } else {
            $('.js-delete-sales').addClass('disabled');
        }
    });

    $('.js-delete-sales').click(handleDeleteClick);
    
    function handleDeleteClick(e) {
        if (gridView.yiiGridView('getSelectedRows').length > 0) {
            if(confirm("Вы уверены что хотите удалить данные записи?") === true) {
                deleteSales();
            }
        }
    }
    
    function deleteSales() {
        let deleteURL = 'batch-delete';
        let data = { 'selected': gridView.yiiGridView('getSelectedRows') };
        $.post(deleteURL, data, function(response) {
            let message = "<b>Удалено: " + response.deleted + '</b><br><br>';
            message += response.errors.map(function(msg) { return '<p class="red">' + msg + '</p>'}).join('');
            alertMessage(message, function() {
                location.reload();
            });
        });
    }
});
JS;

$this->registerJs($js);