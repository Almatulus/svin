<?php

use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model core\models\customer\CustomerCategory */

$this->title = Yii::t('app', 'Customer Categories');
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_customers'></div><h1>{$this->title}</h1>";
?>
<div class="customer-category-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Customer Category'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <br>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function ($model) {
                    return Html::tag('span', FA::icon('tag').' '.$model->name, [
                        'class' => "label label-default label-category",
                        'style' =>
                            "background-color: $model->color; color: $model->fontColor;",
                    ]);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 70px; text-align: center'],
                'template' => "{update} {delete}",
            ],
        ],
    ]); ?>

</div>
