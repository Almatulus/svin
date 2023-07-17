<?php

use core\helpers\order\OrderConstants;
use core\models\order\Order;

return [
    [
        'class' => '\kartik\grid\CheckboxColumn',
        'checkboxOptions' => function (Order $model, $key, $index, $column) {
            return ['disabled' => $model->isEnabled()];
        }
    ],
    'number',
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'status',
        'format' => 'html',
        'value' => function (Order $model) {
            $class = "label";
            $statusName = preg_replace('/ /', '<br>', OrderConstants::getStatuses()[$model->status], 1);
            switch ($model->status) {
                case OrderConstants::STATUS_ENABLED:
                    $class .= " label-warning";
                    break;
                case OrderConstants::STATUS_DISABLED:
                    $class .= " label-danger";
                    break;
                case OrderConstants::STATUS_FINISHED:
                    $class .= " label-success";
                    break;
                default:
                    $class .= " label-danger";
                    break;
            }
            return "<span class='{$class}'>" . $statusName . "</span>";
        },
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'label' => Yii::t('app', 'Session'),
        'format' => 'raw',
        'attribute' => 'datetime',
        'value' => function (Order $model) {
            return Yii::$app->formatter->asDate($model->datetime) . "<br>" .
                Yii::$app->formatter->asTime($model->datetime);
        },
        'contentOptions' => ['class' => 'nowrap'],
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'company_customer_id',
        'format' => 'html',
        'value' => function (Order $model) {
            $customer = $model->companyCustomer->customer;
            $info = [
                $customer->getFullName(),
                $customer->phone,
            ];

            if(isset($customer->iin))
                $info[] = 'Иин: ' . $customer->iin;
            if(isset($customer->id_card_number))
                $info[] = 'Номер карты: ' . $customer->id_card_number;

            return implode(",<br>", $info);
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'format' => 'html',
        'attribute' => 'staff_id',
        'value' => function (Order $model) {
            return $model->staff->getFullName();
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'format' => 'html',
        'label' => Yii::t('app', 'Services'),
        'value' => function (Order $model) {
            return $model->getServicesTitle("<hr>");
        }
    ],
    [
        'format'    => 'html',
        'label'     => Yii::t('app', 'Created By'),
        'attribute' => 'created_time',
        'value'     => function (Order $model) {
            $result = [];

            if ($model->type == OrderConstants::TYPE_MANUAL) {
                if ($model->createdUser->staff) {
                    $result[] = $model->createdUser->staff->getFullName();
                }
            } elseif ($model->type == OrderConstants::TYPE_APPLICATION) {
                $result[] = Yii::t('app', 'application');
            }

            $result[] = Yii::$app->formatter->asDate($model->created_time);
            $result[] = Yii::$app->formatter->asTime($model->created_time);

            return implode('<br>', $result);
        }
    ],
    [
        'attribute' => 'updated_time',
        'format'    => 'datetime',
        'label'     => Yii::t('app', 'Updated at'),
        'value'     => function (Order $model) {
            return $model->getOrderHistory()->orderBy('created_time DESC')->one()->created_time;
        }
    ],
    [
        'attribute' => 'price',
        'format'    => 'decimal',
        'label'     => Yii::t('app', 'Price'),
        'hAlign'    => 'right'
    ],
    [
        'attribute' => 'note',
        'label'     => Yii::t('app', 'Comments')
    ],
    [
        'attribute' => 'companyCustomer.source_id',
        'value'     => 'companyCustomer.source.name',
    ],
];
