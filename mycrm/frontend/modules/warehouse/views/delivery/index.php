<?php

use core\helpers\HtmlHelper as Html;
use core\models\warehouse\Delivery;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\DeliverySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Deliveries');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label'    => $this->title,
    'url'      => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="delivery-index">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id'           => 'deliveries',
            'summary'      => Html::getSummary(),
            'columns'      => [
                [
                    'class' => 'yii\grid\CheckboxColumn'
                ],
                [
                    'format'    => 'html',
                    'attribute' => 'delivery_date',
                    'value'     => function (Delivery $model) {
                        $delivery_date = Yii::$app->formatter->asDate($model->delivery_date);
                        return Html::a($delivery_date, ['view', 'id' => $model->id]);
                    }
                ],
                [
                    'attribute' => 'contractor.name',
                    'label'     => Yii::t('app', 'Contractor')
                ],
                'invoice_number',
                [
                    'format'    => 'datetime',
                    'attribute' => 'created_at',
                ],
                [
                    'label'  => Yii::t('app', "Number of items"),
                    'value'  => function (Delivery $model) {
                        return $model->getProducts()->count();
                    },
                    'hAlign' => 'right',
                ],
                [
                    'attribute' => 'productsTotalCost',
                    'format'    => 'decimal',
                    'label'     => Yii::t('app', 'Total sum'),
                    'hAlign'    => 'right',
                ],
                [
                    'class'    => 'yii\grid\ActionColumn',
                    'template' => '{delete}'
                ]
            ],
        ]); ?>
    </div>

<?php

$js = <<<JS
$(function () {
    var gridView = $('#deliveries');
    
    gridView.on('change', 'input:checkbox', function(e) {
        let keys = gridView.yiiGridView('getSelectedRows');
        if (keys.length > 0) {
            $('.js-delete-deliveries').removeClass('disabled');
        } else {
            $('.js-delete-deliveries').addClass('disabled');
        }
    });

    $('.js-delete-deliveries').click(handleDeleteClick);
    
    function handleDeleteClick(e) {
        if (gridView.yiiGridView('getSelectedRows').length > 0) {
            if(confirm("Вы уверены что хотите удалить данные записи?") === true) {
                deleteDeliveries();
            }
        }
    }
    
    function deleteDeliveries() {
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