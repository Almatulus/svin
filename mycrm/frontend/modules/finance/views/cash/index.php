<?php

use core\models\finance\CompanyCashflow;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $incomeProvider yii\data\ActiveDataProvider */
/* @var $expenseProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Cashes');
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-credit-card"></i> {link}</li>',
    'label'    => $this->title
];
?>
<div class="company-cash-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Cash'), ['create'],
            ['class' => 'btn btn-success']) ?>
    </p>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'id'           => 'customers',
        'options'      => [
            'tag'   => 'table',
            'class' => 'kv-grid-table table table-bordered table-striped kv-table-wrap',
            'id'    => 'list-wrapper',
        ],
        'layout'       => "{summary}<br>\n{pager}\n{items}",
        'itemView'     => '_item',
    ]);
    ?>
    <hr>
    <h2>Список движения средств за последнюю неделю</h2>

    <h3>Поступления</h3>
    <p>
        <?= Html::a(Yii::t('app', 'Create Cashflow Income'), ['cashflow/create-income'], ['class' => 'btn']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $incomeProvider,
        'emptyText' => 'Ничего не найдено.',
        'showOnEmpty' => false,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'date:datetime',
            [
                'attribute' => 'cost_item_id',
                'value' => function (CompanyCashflow $cashflow) {
                    return $cashflow->costItem->getFullName();
                }
            ],
            [
                'attribute' => 'contractor_id',
                'value' => function (CompanyCashflow $cashflow) {
                    return $cashflow->contractor ? $cashflow->contractor->name : null;
                }
            ],
            [
                'attribute' => 'value',
                'value' => function (CompanyCashflow $cashflow) {
                    return Yii::$app->formatter->asDecimal($cashflow->value);
                },
                'hAlign' => 'right',
            ],
            [
                'attribute' => 'comment',
                'format' => 'html',
                'value' => function (CompanyCashflow $model) {
                    $order = $model->order;
                    if ($order!== null) {
                        return \yii\helpers\Html::a($model->comment,
                            ['/order/order/index', 'number' => $order->number, 'from_date' => '', 'to_date' => '']);
                    }
                    return $model->comment;
                }
            ],
        ],
    ]); ?>

    <h3>Отчисления</h3>
    <p>
        <?= Html::a(Yii::t('app', 'Create Cashflow Expense'), ['cashflow/create-expense'], ['class' => 'btn']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $expenseProvider,
        'emptyText' => 'Ничего не найдено.',
        'showOnEmpty' => false,
        'showFooter' => true,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'date:datetime',
            [
                'attribute' => 'cost_item_id',
                'value' => function (CompanyCashflow $cashflow) {
                    return $cashflow->costItem->getFullName();
                }
            ],
            [
                'attribute' => 'contractor_id',
                'value' => function (CompanyCashflow $cashflow) {
                    return $cashflow->contractor ? $cashflow->contractor->name : null;
                }
            ],
            [
                'attribute' => 'value',
                'value' => function (CompanyCashflow $cashflow) {
                    return Yii::$app->formatter->asDecimal($cashflow->value);
                }
            ],
            [
                'attribute' => 'comment',
                'format' => 'html',
                'value' => function (CompanyCashflow $model) {
                    $order = $model->order;
                    if ($order!== null) {
                        return \yii\helpers\Html::a($model->comment,
                            ['/order/order/index', 'number' => $order->number, 'from_date' => '', 'to_date' => '']);
                    }
                    return $model->comment;
                }
            ],
        ],
    ]); ?>
</div>
