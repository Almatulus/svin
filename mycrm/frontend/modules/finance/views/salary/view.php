<?php

/** @var $model \core\models\StaffPayment */

use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = $model->getTitle();

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label'    => Yii::t('app', 'Salary Report'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][]['label'] = $this->title;

$orderPaid = [];
$orderServicePaid = [];
foreach ($model->services as $staffPaymentService) {

    if (!isset($orderPaid[$staffPaymentService->order_service_id])) {
        $orderPaid[$staffPaymentService->order_service_id] = array_reduce($staffPaymentService->orderService->order->cashflows,
            function (int $sum, \core\models\finance\CompanyCashflow $cashflow) {
                return $sum + $cashflow->getValue();
            }, 0);
    }

    $value = min($staffPaymentService->orderService->getFinalPrice(),
        $orderPaid[$staffPaymentService->order_service_id]);
    $orderPaid[$staffPaymentService->order_service_id] -= $value;
    $orderServicePaid[$staffPaymentService->order_service_id] = $value;
}

echo \yii\widgets\DetailView::widget([
    'model'      => $model,
    'attributes' => [
        'start_date',
        'end_date',
        'staff.name',
        'staff.phone',
        'salary:currency'
    ]
]);
?>

    <hr>

    <h4><?= Yii::t('app', 'Services') ?></h4>

<?php
echo GridView::widget([
    'dataProvider'    => new \yii\data\ArrayDataProvider(['models' => $model->services, 'pagination' => false]),
    'showPageSummary' => true,
    'columns'         => [
        [
            'attribute' => 'orderService.divisionService.service_name',
            'format'    => 'html',
            'label'     => Yii::t('app', 'Service'),
            'value'     => function (\core\models\StaffPaymentService $service) {
                return Html::a($service->orderService->divisionService->service_name, [
                    '/order/order/index',
                    'from_date' => "",
                    "to_date"   => "",
                    'number'    => $service->orderService->order->number
                ]);
            }
        ],
        [
            'attribute' => 'orderService.order.datetime',
            'format'    => 'datetime',
            'label'     => Yii::t('app', 'Datetime')
        ],
        'payroll.name:text:' . Yii::t('app', 'Payroll Scheme'),
        'payroll.is_count_discount:boolean:' . Yii::t('app', 'Accommodate discount'),
        [
            'attribute'       => 'orderService.price',
            'format'          => 'decimal',
            'label'           => Yii::t('app', 'Price'),
            'pageSummary'     => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value'           => function (\core\models\StaffPaymentService $model) {
                return $model->payroll->calculateFinalPrice($model->orderService->price,
                    $model->orderService->discount);
            }
        ],
        [
            'format'          => 'decimal',
            'label'           => Yii::t('app', 'Paid'),
            'pageSummary'     => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value'           => function (\core\models\StaffPaymentService $model) use ($orderServicePaid) {
                return $orderServicePaid[$model->order_service_id] ?? 0;
            }
        ],
        [
            'attribute' => 'percent',
            'format'    => 'integer',
            'label'     => Yii::t('app', 'Percent'),
        ],
        [
            'attribute'       => 'sum',
            'format'          => 'decimal',
            'label'           => Yii::t('app', 'Sum'),
            'pageSummary'     => true,
            'pageSummaryFunc' => GridView::F_SUM,
        ],
    ]
]);

?>