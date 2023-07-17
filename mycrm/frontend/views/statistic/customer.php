<?php

use core\forms\customer\statistic\StatisticCustomer;
use core\helpers\HtmlHelper as Html;
use core\models\customer\CustomerCategory;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model core\forms\customer\StatisticForm */

$this->title = Yii::t('app', 'Statistic') . ' - ' . Yii::t('app', 'Customers');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label' => Yii::t('app', 'Statistic'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Customers');

$textAlign = 'text-right';
?>
<div class="customer-loyalty-create">

    <?php $form = ActiveForm::begin([
        'action' => ['customer'],
        'fieldConfig' => ['template' => "{input}\n{hint}\n{error}"],
        'method' => 'get',
    ]); ?>

    <?= $this->render('forms/_customer', [
        'model' => $model,
        'categories' => CustomerCategory::map(),
        'form' => $form,
    ]) ?>

    <div class="col-md-12 column_row">
        <div class="pull-left">
            <?php
            $datetimeTo = new DateTime($model->to);
            $datetimeFrom = new DateTime($model->from);
            $difference = $datetimeTo->diff($datetimeFrom)->days;
            echo sprintf('Выбран период длительностью %d суток', $difference);
            ?>
        </div>
    </div>

    <?php
    $totalRevenue = $dataProvider->query->sum('revenue');
    $totalOrdersCount = $dataProvider->query->sum('orders_count');
    $averageTotalRevenue = $totalOrdersCount == 0 ? 0 : $totalRevenue / $totalOrdersCount;
    ?>
    <div class="col-md-12">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'showFooter' => true,
            'summary' => Html::getSummary(),
            'columns' => [
                [
                    'attribute' => 'customer_name',
                    'label'     => Yii::t('app', 'Customer'),
                    'value'     => function ($data) {
                        return Html::a($data->customer->name . " " . $data->customer->lastname,
                            Url::to(['/customer/customer/view', 'id' => $data->id]));
                    },
                    'footer'    => Yii::t('app', 'Total'),
                    'format'=>'html'
                ],
                [
                    'attribute' => 'customer_phone',
                    'label'     => Yii::t('app', 'Phone'),
                    'value'     => 'customer.phone',
                ],
                [
                    'attribute' => 'average_revenue',
                    'format' => 'decimal',
                    'label' => Yii::t('app', 'Average Revenue'),
                    'hAlign' => 'right',
                    'footer' => Yii::$app->formatter->asDecimal($averageTotalRevenue),
                ],
                [
                    'attribute' => 'orders_count',
                    'value' => function(StatisticCustomer $customer) use($model){
                        return Html::a($customer->orders_count, Url::to([
                            '/order/order/index',
                            'from_date' => $model->from,
                            'to_date' => $model->to,
                            'division_id' => $model->division,
                            'company_customer_id' => $customer->id
                        ]));
                    },
                    'label' => Yii::t('app', 'Orders Count'),
                    'hAlign' => 'right',
                    'footer' => Html::a(number_format($totalOrdersCount, 0, '.', ' '),
                        Url::to([
                            '/order/order/index',
                            'from_date' => $model->from,
                            'to_date' => $model->to,
                            'division_id' => $model->division
                        ])),
                    'format' => 'html',
                ],
                [
                    'attribute' => 'revenue',
                    'format' => 'html',
                    'label' => Yii::t('app', 'Revenue'),
                    'hAlign' => 'right',
                    'value' => function(StatisticCustomer $customer) use($model){
                        return Html::a(Yii::$app->formatter->asDecimal($customer->revenue),
                            Url::to([
                                '/finance/cashflow/index',
                                'sFrom' => $model->from,
                                'sTo' => $model->to,
                                'sDivision' => $model->division,
                                'sCustomer' => $customer->id
                            ]));
                    },
                    'footer' => Html::a(Yii::$app->formatter->asDecimal($totalRevenue),
                        Url::to([
                            '/finance/cashflow/index',
                            'sFrom' => $model->from,
                            'sTo' => $model->to,
                            'sDivision' => $model->division
                        ])),
                ],
                [
                    'attribute' => 'revenue',
                    'label'     => Yii::t('app', '% from Total Revenue'),
                    'value'     => function (StatisticCustomer $customer) use ($totalRevenue) {
                        if ($totalRevenue != 0)
                            return Yii::$app->formatter->asPercent($customer->revenue / $totalRevenue);
                        else
                            return '0 %';
                    },
                    'hAlign'    => 'right',
                ],
            ],
        ]); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
