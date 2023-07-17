<?php

use core\helpers\HtmlHelper as Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Products');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label' => $this->title,
    'url' => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="product-index">
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php \yii\widgets\Pjax::begin([
        'id' => 'pjax-container',
        'timeout' => 10000,
        'clientOptions' => ['container' => 'pjax-container']]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id'           => 'products',
            'responsiveWrap' => false,
            'summary' => Html::getSummary(),
            'columns' => [
                ['class' => 'yii\grid\CheckboxColumn'],
                [
                    'attribute' => 'name',
                    'format' => 'html',
                    'value' => function($model) {
                        return Html::a($model->name, ['update', 'id' => $model->id]);
                    }
                ],
                [
                    'attribute' => 'categoryName',
                    'label' => Yii::t('app', 'Category'),
                    'value' => 'category.name'
                ],
                [
                    'attribute' => 'types',
                    'format' => 'html',
                    'value' => function($model) {
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
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}'
                ],
            ],
        ]); ?>

    <?php \yii\widgets\Pjax::end(); ?>
</div>
