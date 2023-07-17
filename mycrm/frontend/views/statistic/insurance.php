<?php

use core\models\company\Insurance;
use core\models\order\Order;
use core\models\Staff;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/**
 * @var $searchModel \core\forms\statistic\InsuranceStatForm
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var \core\models\order\Order[] $models
 */

$this->title = Yii::t('app', 'Registry for insured customers');

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label'    => Yii::t('app', 'Statistic'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

$orderPaid = [];
$orderServicePaid = [];

$totalPrice = (clone($dataProvider->query))->distinct(false)->select('{{%order_services}}.price')->groupBy('{{%order_services}}.id')->sum('price');
$totalQuantity = (clone($dataProvider->query))->distinct(false)->select('{{%order_services}}.quantity')->groupBy('{{%order_services}}.id')->sum('quantity');
$totalSum = (clone($dataProvider->query))->distinct(false)->groupBy('{{%orders}}.id')->sum('price');
$totalDiscountPrice = (clone($dataProvider->query))->distinct(false)->select('{{%order_services}}.price, {{%order_services}}.discount')
    ->andWhere(['<>', "{{%order_services}}.discount", 100])
    ->groupBy('{{%order_services}}.id')
    ->sum('price * discount / 100');
$totalInsurancePayment = (clone($dataProvider->query))->distinct(false)->select('{{%order_payments}}.amount')
    ->joinWith('orderPayments.payment', false)
    ->andWhere(["{{%payments}}.type" => \core\helpers\company\PaymentHelper::INSURANCE])
    ->groupBy('{{%order_payments}}.id')
    ->sum('amount');
?>

<?php $form = ActiveForm::begin(['method' => 'get', 'action' => 'insurance']); ?>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($searchModel, 'from', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'From date') . '</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($searchModel, 'to', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'To date') . '</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-3">
            <?php if ($searchModel->service_id) {
                $service = \core\models\division\DivisionService::findOne($searchModel->service_id);
            } ?>
            <?= $form->field($searchModel, 'service_id')->widget(Select2::class,
                [

                    'initValueText' => isset($service) ? $service->service_name : '',
                    'pluginOptions' => [
                        'allowClear'         => true,
                        'minimumInputLength' => 3,
                        'ajax'               => [
                            'url'      => Url::to(['/division/service/search']),
                            'dataType' => 'json',
                            'data'     => new JsExpression('function(params) { return {name:params.term}; }')
                        ],
                        'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                        'templateResult'     => new JsExpression('function(object) {
                            if (object.id && object.options) {
                                return object.options.service_name;
                            }
                            return object.text;
                         }'),
                        'templateSelection'  => new JsExpression('function (object) {
                            if (object.id && object.options) {
                                return object.options.service_name;
                            }
                            return object.text;
                        }'),
                        'size'               => 'sm',
                    ],
                    'options'       => [
                        'placeholder' => Yii::t('app', 'Select service')
                    ],
                    'size'          => Select2::SMALL
                ])->label(false); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($searchModel, 'insurance_company_id')->widget(Select2::class,
                [
                    'data'          => Insurance::map(),
                    'options'       => [
                        'placeholder' => Yii::t('app', 'Select company')
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                    'size'          => Select2::SMALL
                ])->label(false); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($searchModel, 'staff_id')->widget(Select2::class,
                [
                    'data'          => Staff::getOwnCompanyStaffList(),
                    'options'       => [
                        'placeholder' => Yii::t('app', 'Select staff')
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                    'size'          => Select2::SMALL
                ])->label(false); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('app', 'Export'), array_merge(['export-insurance'], Yii::$app->request->queryParams),
                    ['class' => 'btn btn-default']
                ) ?>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>

<?= \kartik\grid\GridView::widget([
    'dataProvider'   => $dataProvider,
    'responsiveWrap' => true,
    'showFooter'     => true,
    'columns'        => [
        [
            'attribute' => 'datetime',
            'format'    => 'date',
            'label'     => Yii::t('app', 'Date')
        ],
        [
            'attribute' => 'customer_name',
            'label'     => Yii::t('app', 'Customer'),
            'value'     => function (\core\models\order\Order $model) {
                return Html::a($model->companyCustomer->customer->fullName, Url::to([
                    '/customer/customer/view',
                    'id' => $model->companyCustomer->id
                ]));
            },
            'format'    => 'html',
        ],
        [
            'attribute' => 'customer_policy',
            'label'     => Yii::t('app', 'Policy'),
            'value'     => 'companyCustomer.insurance_policy_number',
        ],
        [
            'attribute' => 'staff_name',
            'label'     => Yii::t('app', 'Staff ID'),
            'value'     => function (\core\models\order\Order $model) {
                return Html::a($model->staff->name, Url::to([
                    '/staff/view',
                    'id' => $model->staff->id
                ]));
            },
            'format'    => 'html',
        ],
        [
            'attribute' => 'insurance_company',
            'label'     => Yii::t('app', 'Company'),
            'value'     => 'insuranceCompany.name',
        ],
        [
            'attribute' => 'service_name',
            'label'     => Yii::t('app', 'Service'),
            'value'     => function (\core\models\order\Order $model) {
                return $model->getServicesTitle("<br><br>");
            },
            'format'    => 'html',
        ],
        [
            'attribute' => 'price',
            'format'    => 'html',
            'footer'    => Yii::$app->formatter->asDecimal($totalPrice),
            'label'     => Yii::t('app', 'Price'),
            'value'     => function (\core\models\order\Order $model) {
                return implode("<br><br>", array_map(function (\core\models\order\OrderService $service) {
                    return Yii::$app->formatter->asDecimal($service->price);
                }, $model->orderServices));
            },
        ],
        [
            'attribute' => 'quantity',
            'format'    => 'html',
            'footer'    => $totalQuantity,
            'label'     => Yii::t('app', 'Quantity'),
            'value'     => function (\core\models\order\Order $model) {
                return implode("<br><br>", array_map(function (\core\models\order\OrderService $service) {
                    return $service->quantity;
                }, $model->orderServices));
            },
        ],
        [
            'attribute' => 'discount',
            'format'    => 'html',
            'label'     => Yii::t('app', 'Discount, %'),
            'value'     => function (\core\models\order\Order $model) {
                return implode("<br><br>", array_map(function (\core\models\order\OrderService $service) {
                    return $service->discount;
                }, $model->orderServices));
            },
        ],
        [
            'attribute' => 'discount_sum',
            'label'     => Yii::t('app', 'Discount, currency'),
            'format'    => 'html',
            'footer'    => Yii::$app->formatter->asDecimal($totalDiscountPrice),
            'value'     => function (\core\models\order\Order $model) {
                return implode("<br><br>", array_map(function (\core\models\order\OrderService $service) {
                    return Yii::$app->formatter->asDecimal($service->getDiscountPrice());
                }, $model->orderServices));
            },
        ],
        [
            'attribute' => 'sum',
            'format'    => 'decimal',
            'footer'    => Yii::$app->formatter->asDecimal($totalSum),
            'label'     => Yii::t('app', 'Sum'),
            'value'     => 'price'
        ],
        [
            'format' => 'decimal',
            'footer' => Yii::$app->formatter->asDecimal($totalInsurancePayment),
            'label'  => "Оплачено страховкой",
            'value'  => function (Order $order) {
                return array_reduce($order->orderPayments,
                    function ($sum, \core\models\order\OrderPayment $orderPayment) {
                        return $sum + ($orderPayment->payment->isInsurance() ? $orderPayment->amount : 0);
                    }, 0);
            }
        ]
    ]
]);