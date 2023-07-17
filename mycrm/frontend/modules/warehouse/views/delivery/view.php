<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Delivery */

$this->title = Yii::t('app', 'Delivery') . ' #' .$model->id;
$this->params['breadcrumbs'][]    = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 
    'label' => Yii::t('app', 'Deliveries'), 
    'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>

<?= $this->render('/common/_tabs') ?>

<div class="delivery-view">

    <h2>
        Детали поставки:
        <?= $model->getProducts()->count() ?> товара
        (<?= Yii::$app->formatter->asDecimal($model->productsTotalCost) ?>)
        <?= Html::a(
            Yii::t('app', 'Update'),
            ['update', 'id' => $model->id],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>

    <div class="column_row data_table">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => new \yii\data\ActiveDataProvider([
                'query' => $model->getProducts(),
            ]),
            'layout'       => "{items}{pager}",
            'showFooter'   => true,
            'columns'      => [
                ['class' => 'yii\grid\SerialColumn'],
                'product.name',
                [
                    'attribute' => 'product.unit.name',
                    'label' => Yii::t('app', 'Unit')
                ],
                'quantity',
                [
                    'attribute' => 'price',
                    'format' => 'decimal'
                ],
                [   
                    'attribute' => 'sum',
                    'format' => 'decimal',
                    'footer' => "Итого <strong><b>" . Yii::$app->formatter->asDecimal($model->productsTotalCost) . "</b></strong>",
                    'contentOptions' => ['class' => 'right_text'],
                    'footerOptions' => ['class' => 'right_text'],
                    'headerOptions' => ['class' => 'right_text'],
                ]
            ]
        ]);
        ?>
    </div>

    <div class="column-row">
        <?= DetailView::widget([
            'options' => ['class' => 'info_table'],
            'template' => '<tr><td class="key">{label}</td><td class="value">{value}</td></tr>',
            'model' => $model,
            'attributes' => [
                [
                    'attribute' => 'contractor.name',
                    'label' => $model->getAttributeLabel('contractor_id')    
                ],
                'invoice_number',
                'delivery_date',
                'notes',
                'created_at:datetime',
                [
                    'attribute' => 'creator.name',
                    'label' => $model->getAttributeLabel('creator_id')    
                ]
            ],
        ]) ?>
    </div>

</div>
