<?php

use core\helpers\HtmlHelper as Html;
use core\models\warehouse\Product;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Archive');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label' => $this->title,
    'url' => ['archive']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="product-archive">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'responsiveWrap' => false,
        'summary' => Html::getSummary(),
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'html',
//                'value' => function($model) {
//                    return Html::a($model->name, ['update', 'id' => $model->id]);
//                }
            ],
            [
                'attribute' => 'categoryName',
                'label' => Yii::t('app', 'Category'),
                'value' => 'category.name'
            ],
            [
                'attribute' => 'types',
                'format' => 'html',
                'value' => function(Product $model) {
                    return $model->getTypesTitle('<br>');
                }
            ],
            'sku',
            [
                'attribute' => 'unit.name',
                'label' => Yii::t('app', 'Unit')
            ],
            'quantity',
            [
                'attribute' => 'purchase_price',
                'format' => 'decimal'
            ],
            [
                'attribute' => 'price',
                'format' => 'decimal'
            ],
            [
                'class'    => 'yii\grid\ActionColumn',
                'buttons'  => [
                    'restore' => function ($url, $model, $key) {
                        return Html::a(Yii::t('app', 'Restore'),
                            ['restore', 'id' => $model->id],
                            [
                                'class'        => 'btn btn-default',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to restore this product?'),
                                'data-method'  => 'post',
                            ]
                        );
                    }
                ],
                'template' => '{restore}'
            ],
        ],
    ]); ?>
</div>
