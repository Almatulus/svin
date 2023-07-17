<?php

use core\forms\finance\SalaryCheckoutForm;
use core\forms\finance\SalaryForm;
use core\models\order\OrderService;
use kartik\date\DatePicker;
use kartik\daterange\DateRangePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model SalaryForm */
/* @var $checkoutForm SalaryCheckoutForm */
/* @var $staffs array[] */
/* @var $divisions array[] */
/* @var $items array[] */

$this->title = Yii::t('app', 'Payroll Staff');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Payroll Schemes'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

$totalSalary = array_reduce($dataProvider->getModels(), function ($total, OrderService $model) {
    return $total + $model->getPaymentAmount();
}, 0);

if (is_null($checkoutForm->salary)) {
    $checkoutForm->salary = $totalSalary;
}

$orderPaid = [];
$orderServicePaid = [];
foreach ($dataProvider->models as $orderService) {
    /** @var OrderService $orderService */

    if (!isset($orderPaid[$orderService->order_id])) {
        $orderPaid[$orderService->order_id] = array_reduce($orderService->order->cashflows,
            function (int $sum, \core\models\finance\CompanyCashflow $cashflow) {
                return $sum + $cashflow->getValue();
            }, 0);
    }

    $value = min($orderService->getFinalPrice(), $orderPaid[$orderService->order_id]);
    $orderPaid[$orderService->order_id] -= $value;
    $orderServicePaid[$orderService->id] = $value;
}

?>

    <div class="scheme-pay-salary">

        <h2>Оклад</h2>

        <?php $form = ActiveForm::begin([
            'action' => ['estimate'],
            'method' => 'get'
        ]); ?>

        <?= $form->errorSummary($model); ?>

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($model, 'division_id')
                    ->dropDownList($divisions, ['prompt' => Yii::t('app', 'Select division')])
                    ->label(Yii::t('app', 'Division ID') . ': ');
                ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'staff_id')
                    ->widget(\kartik\select2\Select2::class, [
                        'data'          => $staffs,
                        'options'       => ['placeholder' => Yii::t('app', 'Select staff')],
                        'pluginOptions' => ['allowClear' => true],
                        'size'          => \kartik\select2\Select2::SMALL
                    ])
                    ->label(Yii::t('app', 'Staff ID') . ': ');
                ?>
            </div>
            <div class="col-md-6">
                <?php
                echo $form->field($model, 'date_range', [
                    'options' => ['class' => 'drp-container form-group']
                ])->widget(DateRangePicker::class, [
                    'useWithAddon'   => false,
                    'convertFormat'  => true,
                    'presetDropdown' => true,
                    'hideInput'      => true,
                    'pluginOptions'  => [
                        'timePicker' => false,
                        'locale'     => ['format' => 'Y-m-d'],
                    ]
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button class="btn btn-primary pull-right" type="submit">Показать</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

        <hr/>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->errorSummary($checkoutForm); ?>

        <?php if ($checkoutForm->hasErrors("services")) {
            echo $form->field($checkoutForm, "ignore_warnings")->checkbox(['label' => "Игнорировать предупреждения"]);
        } ?>

        <h2>Проценты от услуг</h2>
        <span>*Зарплата считается со стоимости услуги</span>
        <?= GridView::widget([
            'showHeader'      => true,
            'dataProvider'    => $dataProvider,
            'responsiveWrap'  => false,
            'tableOptions'    => ['class' => 'table table-condensed'],
            'layout'          => "{items}\n{pager}",
            'columns'         => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'label'     => Yii::t('app', 'Services'),
                    'attribute' => 'divisionService.service_name'
                ],
                [
                    'attribute'      => 'order.datetime',
                    'contentOptions' => function (OrderService $service) use ($checkoutForm) {
                        $orderDate = (new \DateTime($service->order->datetime))->setTime(0, 0, 0);
                        if ($orderDate > (new \DateTime($checkoutForm->payment_date))) {
                            return ['class' => 'red'];
                        }
                        return [];
                    },
                    'format'         => ['date', 'php:d.m, H:i'],
                    'label'          => Yii::t('app', 'Datetime'),
                    'pageSummary'    => 'Итого',
                ],
                [
                    'attribute' => 'payroll.name',
                    'label'     => Yii::t('app', 'Payroll Scheme'),
                ],
                [
                    'attribute' => 'payroll.is_count_discount',
                    'format'    => 'boolean',
                    'label'     => Yii::t('app', 'Accommodate discount')
                ],
                [
                    'contentOptions' => ['class' => 'service-service_mode'],
                    'hidden'         => true,
                    'value'          => function (OrderService $service) {
                        return $service->getPayroll()->getPayrollScheme($service->division_service_id)->service_mode;
                    }
                ],
                [
                    'attribute'       => 'quantity',
                    'contentOptions'  => ['class' => 'service-quantity'],
                    'pageSummary'     => true,
                    'pageSummaryFunc' => GridView::F_SUM
                ],
                [
                    'format'          => 'integer',
                    'attribute'       => 'price',
                    'value'           => function (OrderService $service) {
                        $payroll = $service->getPayroll();
                        return $payroll->is_count_discount ? $service->getFinalPrice() : $service->price;
                    },
                    'contentOptions'  => ['class' => 'service-price'],
                    'label'           => Yii::t('app', 'Cost, services'),
                    'pageSummary'     => true,
                    'pageSummaryFunc' => GridView::F_SUM
                ],
                [
                    'format'          => 'integer',
                    'attribute'       => 'paid',
                    'label'           => Yii::t('app', 'Paid'),
                    'value'           => function (OrderService $service) use ($orderServicePaid) {
                        return $orderServicePaid[$service->id];
                    },
                    'contentOptions' => function(OrderService $model, $key, $index, $column) use ($orderServicePaid)  {
                        $total_price = $model->getFinalPrice();
                        $paid =  $orderServicePaid[$model->id];
                        if ($paid < $total_price) {
                            return ['style' => 'color: red'];
                        } else {
                            return [];
                        }
                    },
                    'pageSummary'     => true,
                    'pageSummaryFunc' => GridView::F_SUM
                ],
                [
                    'format'         => 'raw',
                    'label'          => Yii::t('app', 'Motivation'),
                    'value'          => function (OrderService $service) use ($checkoutForm) {
                        $value = $checkoutForm->services[$service->id]['percent'] ?? $service->getPaymentPercent();
                        $scheme = $service->getPayroll()->getPayrollScheme($service->division_service_id);
                        $sign = $scheme->service_mode === \core\models\finance\Payroll::PAYROLL_MODE_PERCENTAGE ? "%" : "тг";

                        return Html::textInput("services[{$service->id}][percent]", $value, [
                                'class'   => 'payroll-percent',
                                'type'    => 'number',
                                'data-id' => $service->id,
                                'style'   => 'width: 90px;'
                            ]) . "&nbsp" . $sign;
                    },
                    'contentOptions' => ['width' => '15%']
                ],
                [
                    'format'             => 'raw',
                    'label'              => Yii::t('app', 'Wage'),
                    'value'              => function (OrderService $service) {
                        $value = $checkoutForm->services[$service->id]['percent'] ?? $service->getPaymentAmount();
                        return Html::textInput("services[{$service->id}][sum]", $value, [
                            'class'   => 'payroll-amount',
                            'type'    => 'number',
                            'data-id' => $service->id,
                            'style'   => 'width: 90px;'
                        ]). "&nbspтг";
                    },
                    'pageSummary'        => Yii::$app->formatter->asDecimal($totalSalary),
                    'contentOptions'     => ['width' => '15%'],
                    'pageSummaryOptions' => ['class' => 'payment-amount-total']
                ],
                [
                    'label'     => Yii::t('app', 'Is Paid'),
                    'value' => function (OrderService $service) {
                        return $service->order->is_paid ? 'Paid' : 'Not paid';
                    }
                ],
            ],
            'showPageSummary' => true
        ]); ?>

        <hr>

        <h2>Зарплата</h2>

        <?= $form->field($checkoutForm, 'salary')->textInput(['class' => 'form-control'])->label(false) ?>

        <?= $form->field($checkoutForm, "payment_date")
            ->widget(DatePicker::class, [
                'options' => ['placeholder' => Yii::t('app', 'Payment date')],
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ]
            ]); ?>

        <div class="form-group">
            <?= Html::submitButton('Выдать зарплату', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

<?php

require_once('_script.php');

