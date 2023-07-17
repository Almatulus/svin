<?php

namespace core\services;

use common\components\excel\Excel;
use core\models\order\OrderPayment;
use Yii;

class StatisticService
{
    /**
     * @param \core\models\order\Order[] $models
     */
    public function exportInsurance($models)
    {
        $totalPrice = $totalQuantity = $totalDiscountPrice = $totalSum = $totalInsurancePayment = 0;
        foreach ($models as $model) {
            foreach ($model->orderServices as $orderService) {
                $totalPrice += $orderService->price;
                $totalQuantity += $orderService->quantity;
                $totalDiscountPrice += $orderService->getDiscountPrice();
                $totalSum += $model->price;
            }

            $totalInsurancePayment += array_reduce($model->orderPayments, function ($sum, OrderPayment $orderPayment) {
                return $sum + ($orderPayment->payment->isInsurance() ? $orderPayment->amount : 0);
            }, 0);
        }

        $columns = [
            [
                'attribute' => 'datetime',
                'format'    => 'datetime'
            ],
            [
                'attribute' => 'company_customer_id',
                'value'     => 'companyCustomer.customer.fullName',
            ],
            [
                'attribute' => 'companyCustomer.insurance_policy_number',
                'label'     => Yii::t('app', 'Policy')
            ],
            [
                'attribute' => 'staff_id',
                'value'     => 'staff.name',
            ],
            [
//                'attribute' => 'orderService.division_service_id',
                'label' => Yii::t('app', 'Service'),
                'value' => function (\core\models\order\Order $model) {
                    return $model->getServicesTitle("\n");
                },
            ],
            [
                'attribute' => 'insuranceCompany.name',
                'label'     => Yii::t('app', 'Company'),
            ],
            [
                'attribute' => 'price',
                'format'    => 'number',
                'footer'    => $totalPrice,
                'value'     => function (\core\models\order\Order $model) {
                    return implode("\n", array_map(function (\core\models\order\OrderService $service) {
                        return Yii::$app->formatter->asDecimal($service->price);
                    }, $model->orderServices));
                }
            ],
            [
//                'attribute' => 'quantity',
                'label'  => Yii::t('app', 'Quantity'),
                'format' => 'number',
                'footer' => $totalQuantity,
                'value'  => function (\core\models\order\Order $model) {
                    return implode("\n", array_map(function (\core\models\order\OrderService $service) {
                        return Yii::$app->formatter->asDecimal($service->quantity);
                    }, $model->orderServices));
                }
            ],
            [
//                'attribute' => 'discount',
                'format' => 'number',
                'label'  => Yii::t('app', 'Discount, %'),
                'value'  => function (\core\models\order\Order $model) {
                    return implode("\n", array_map(function (\core\models\order\OrderService $service) {
                        return Yii::$app->formatter->asDecimal($service->discount);
                    }, $model->orderServices));
                }
            ],
            [
//                'attribute' => 'discountPrice',
                'format' => 'number',
                'label'  => Yii::t('app', 'Discount, currency'),
                'footer' => $totalDiscountPrice,
                'value'  => function (\core\models\order\Order $model) {
                    return implode("\n", array_map(function (\core\models\order\OrderService $service) {
                        return Yii::$app->formatter->asDecimal($service->getDiscountPrice());
                    }, $model->orderServices));
                }
            ],
            [
                'attribute' => 'price',
                'format'    => 'number',
                'footer'    => $totalSum,
            ],
            [
//                'attribute' => 'cashflow.value',
                'format' => 'number',
                'footer' => $totalInsurancePayment,
                'label'  => "Оплачено страховкой",
                'value'  => function (\core\models\order\Order $model) {
                    return array_reduce($model->orderPayments,
                        function ($sum, \core\models\order\OrderPayment $orderPayment) {
                            return $sum + ($orderPayment->payment->isInsurance() ? $orderPayment->amount : 0);
                        }, 0);
                }
            ]
        ];

        $excelService = new Excel([
            'showFooter' => true,
            'models'     => $models,
            'columns'    => $columns,
            'creator'    => \Yii::$app->name,
            'title'      => \Yii::t('app', "Insurances"),
            'filename'   => \Yii::t('app', "Insurances") . "_" . date("d-m-Y-His"),
        ]);
        $excelService->export();
    }

    /**
     * @param \core\forms\customer\statistic\StatisticService $model
     * @param \core\forms\customer\statistic\StatisticService[] $services
     */
    public function exportServices($model, $services)
    {
        $totalRevenue = $totalOrdersCount = $totalAverageCost = 0;

        foreach ($services as $service) {
            $service->from = $model->from;
            $service->to = $model->to;

            $totalOrdersCount += $service->services_count;
            $totalRevenue += $service->revenue;
            $totalAverageCost += $service->average_cost;
        }

        $columns = [
            [
                'attribute' => 'service_name',
                'footer' => Yii::t('app', 'Total'),
            ],
            [
                'attribute' => 'services_count',
                'label' => Yii::t('app', 'Orders Count Service'),
                'footer' => Yii::$app->formatter->asDecimal($totalOrdersCount),
                'format' => 'number'
            ],
            [
                'attribute' => 'revenue',
                'label' => Yii::t('app', 'Revenue'),
                'format' => 'number',
                'footer' => Yii::$app->formatter->asDecimal($totalRevenue),
                'hAlign'    => 'right',
            ],
            [
                'attribute' => 'average_cost',
                'format' => 'number',
                'label' => Yii::t('app', 'Average Cost'),
                'value' => function(\core\forms\customer\statistic\StatisticService $model) {
                    return $model->revenue / $model->orders_count;
                },
                'footer' => Yii::$app->formatter->asDecimal($totalAverageCost),
                'hAlign'    => 'right',
            ],
            [
                'attribute' => 'revenue',
                'label'     => Yii::t('app', '% from Total Revenue'),
                'value'     => function (\core\forms\customer\statistic\StatisticService $service) use ($totalRevenue) {
                    if ($totalRevenue != 0)
                        return Yii::$app->formatter->asPercent($service->revenue / $totalRevenue);
                    else
                        return '0 %';
                },
                'hAlign'    => 'right',
                'format' => 'number'
            ],
        ];

        $excelService = new Excel([
            'showFooter' => true,
            'models'     => $services,
            'columns'    => $columns,
            'creator'    => \Yii::$app->name,
            'title'      => \Yii::t('app', "Services"),
            'filename'   => \Yii::t('app', "Services") . "_" . date("d-m-Y-His"),
        ]);
        $excelService->export();
    }
}