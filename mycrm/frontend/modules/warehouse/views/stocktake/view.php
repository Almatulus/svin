<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Stocktake */

$this->title                   = $model->name;
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label'    => Yii::t('app', 'Stocktakes'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/common/_tabs') ?>

    <div class="column_row">
        <h2>
            <?= $model->title ?>
            <div class="stocktaking_finished">завершено</div>
        </h2>
    </div>

    <div class="column_row">
        <?= DetailView::widget([
            'options'    => ['class' => 'info_table compact vertical'],
            'template'   => '<tr><td class="key">{label}</td><td class="value">{value}</td></tr>',
            'model'      => $model,
            'attributes' => [
                [
                    'attribute' => 'created_at',
                    'format'    => 'date',
                ],
                [
                    'attribute' => 'creator_id',
                    'value'     => $model->creator->name
                ],
                'numberOfProducts',
                'description',
            ],
        ]) ?>
    </div>

    <div class="column_row">
        <?= DetailView::widget([
            'options'    => ['class' => 'info_table compact vertical'],
            'template'   => '<tr><td class="key">{label}</td><td class="value">{value}</td></tr>',
            'model'      => $model,
            'attributes' => [
                'accurateProductsCount',
                'productsWithShortageCount',
                'productsWithSurplusCount'
            ],
        ]) ?>
    </div>

<?= \yii\grid\GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'allModels'  => $model->products,
        'pagination' => false,
    ]),
    'options'      => ['class' => 'column_row data_table'],
    'layout'       => "{items}",
    'columns'      => [
        [
            'class' => 'yii\grid\SerialColumn',
        ],
        'product.name',
        [
            'attribute' => 'product.types',
            'format'    => 'html',
            'value'     => function ($model) {
                return $model->product->getTypesTitle('<br>');
            }
        ],
        'recorded_stock_level',
        'actual_stock_level',
        [
            'format'         => 'html',
            'attribute'      => 'balanceText',
            'contentOptions' => function ($model, $key, $index, $column) {
                if ($model->balance == 0) {
                    return ['class' => 'positive'];
                }

                return ['class' => 'negative'];
            }
        ],
        [
            'format'         => 'html',
            'attribute'      => 'estimatedVarianceText',
            'contentOptions' => function ($model, $key, $index, $column) {
                if ($model->balance == 0) {
                    return ['class' => 'positive'];
                }

                return ['class' => 'negative'];
            }
        ]
    ]
]);
