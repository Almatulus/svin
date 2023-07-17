<?php
use core\helpers\order\OrderConstants;
use core\models\order\Order;

return [
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'status',
        'format' => 'html',
        'value' => function (Order $data) {
            $class = "label";
            $statusName = preg_replace('/ /', '<br>', OrderConstants::getStatuses()[$data->status], 1);
            switch ($data->status) {
                case OrderConstants::STATUS_ENABLED:
                    $class .= " label-warning";
                    break;
                case OrderConstants::STATUS_DISABLED:
                    $class .= " label-danger";
                    break;
                case OrderConstants::STATUS_FINISHED:
                    $class .= " label-success";
                    break;
            }
            return "<span class='{$class}'>" . $statusName . "</span>";
        },
    ],
    [
        'attribute' => 'number',
        'format'=> 'html',
        'value' => function(Order $data) {
            return \yii\bootstrap\Html::a($data->number, ['view', 'id' => $data->id]);
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'label' => Yii::t('app', 'Session'),
        'format' => 'raw',
        'value' => function (Order $data) {
            return Yii::$app->formatter->asDate($data->datetime) . "<br>" .
                Yii::$app->formatter->asTime($data->datetime);
        },
        'contentOptions' => ['class' => 'nowrap'],
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'company_customer_id',
        'format' => 'html',
        'value' => function ($data) {
            $customer = $data->companyCustomer->customer;
            $info = [
                $customer->name,
                $customer->phone,
            ];
            return implode(",<br>", $info);
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'format' => 'html',
        'width' => '200px',
        'attribute' => 'staff_id',
        'value' => function (Order $model) {
            $staff_name = null;
            if ($model->staff_id) {
                $staff_name = $model->staff->getFullName();
            }
            $info = [
                $model->division->getTotalName(),
                $staff_name,
            ];
            return implode(",<br>", $info);
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'format' => 'html',
        'label' => Yii::t('app', 'Services'),
        'value' => function (Order $data) {
            return $data->getServicesTitle("<br>");
        }
    ],
    [
        'format' => 'html',
        'label' => Yii::t('app', 'Created By'),
        'attribute' => 'created_user_id',
        'value' => function (Order $data) {
            $date = Yii::$app->formatter->asDatetime(new DateTime($data->created_time));
            if ($data->type == OrderConstants::TYPE_MANUAL) {
                return $data->createdUser->name . "<br/>" . $date;
            } elseif ($data->type == OrderConstants::TYPE_APPLICATION) {
                return Yii::t('app', 'application') . "<br/>" . $date;
            }
            return $date;
        }
    ],
    [
        'attribute' => 'price',
        'label' => Yii::t('app', 'Price'),
        'value' => function (Order $data) {
            return Yii::$app->formatter->asDecimal($data->price);
        },
        'hAlign' => 'right'
    ],
];