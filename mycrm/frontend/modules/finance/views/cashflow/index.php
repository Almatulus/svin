<?php

use core\helpers\HtmlHelper as Html;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use frontend\modules\finance\search\CashflowSearch;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\finance\search\CashflowSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Company Cashflows');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_services"></div>{link}</li>',
    'label' => $this->title
];
?>
<div class="company-cashflow-index">

    <?= $this->render('_search', ['searchModel' => $searchModel])?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary'      => Html::getSummary(),
        'showFooter'   => true,
        'rowOptions'   => function (CompanyCashflow $model) {
            if ($model->costItem->type == CompanyCostItem::TYPE_EXPENSE) {
                return ['class' => 'red'];
            }
        },
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],
            'date:datetime',
            [
                'attribute' => 'order_id',
                'label' => Yii::t('app', 'Order model'),
                'value'     => function (CompanyCashflow $model) {
                    if (!$model->order_id) {
                        return null;
                    }
                    $order = $model->order;
                    $datetime = Yii::$app->formatter->asDatetime($order->datetime);
                    return Html::a($datetime, [
                            '/order/order/index',
                            'number' => $order->number,
                            'division_id' => $order->division_id,
                            'from_date' => '',
                            'to_date' => ''
                    ]);
                },
                'format'    => 'html',
                'group'     => true,
            ],
            [
                'attribute' => 'cost_item_id',
                'value' => function (CompanyCashflow $model) {
                    return $model->costItem->getFullName();
                },
            ],
            [
                'attribute' => 'cash_id',
                'format' => 'html',
                'value' => function (CompanyCashflow $model) {
                    return Html::a($model->cash->name, ['/finance/cash/view', 'id' => $model->cash_id]);
                },
            ],
            [
                'attribute' => 'staff_id',
                'format' => 'html',
                'value' => function (CompanyCashflow $model) {
                    if (!$model->staff_id) {
                        return null;
                    }
                    $staff_name = $model->staff->getFullName();
                    return Html::a($staff_name, ['/staff/view', 'id' => $model->staff_id]);
                },
            ],
            [
                'attribute' => 'customer_id',
                'format' => 'html',
                'value' => function (CompanyCashflow $model) {
                    if (!$model->customer_id) {
                        return null;
                    }
                    $customer_name = $model->customer->customer->getFullInfo();
                    return Html::a($customer_name, ['/customer/customer/view', 'id' => $model->customer_id]);
                },
            ],
            [
                'attribute' => 'contractor_id',
                'value' => function (CompanyCashflow $model) {
                    return $model->contractor_id ? $model->contractor->name : null;
                },
                'footer' => Yii::t('app', 'Total')
            ],
            [
                'attribute' => 'created_at',
                'format' => 'html',
                'value' => function (CompanyCashflow $model) {
                    return $model->user ? implode('<br/>', [
                            $model->user->getFullName(),
                            Yii::$app->formatter->asDatetime($model->created_at, 'php:d F Y H:i:s')
                        ]) : '';
                },
                'group' => true,
                'subGroupOf' => 1,
            ],
            [
                'attribute' => 'value',
                'format' => 'decimal',
                'hAlign'    => 'right',
                'footer'    => CashflowSearch::getTotalValue($dataProvider->query),
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
            [
                'class'          => 'yii\grid\ActionColumn',
                'buttons'        => [
                    'delete-debt-payment' => function ($url, $model, $key) {
                        $title = Yii::t('app', 'Delete');
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-trash"]);
                        return Html::a($icon, ['delete-debt-payment', 'id' => $model->id], [
                            'title'        => $title,
                            'aria-label'   => $title,
                            'data-pjax'    => '0',
                            'data-confirm' => Yii::t('app', 'Are you sure you want to delete this record?'),
                            'data-method'  => 'post',
                        ]);
                    },
                ],
                'template'       => '{update} {delete} {delete-debt-payment}',
                'visibleButtons' => [
                    'delete'              => function (CompanyCashflow $model, $key, $index) {
                        return $model->isEditable();
                    },
                    'delete-debt-payment' => function (CompanyCashflow $model, $key, $index) {
                        return $model->isDeletableDebtPayment();
                    }
                ],
            ]
        ],
    ]); ?>

</div>
