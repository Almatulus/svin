<?php


use core\models\user\User;
use core\helpers\order\OrderConstants;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model core\models\order\Order */
?>
<div class="order-view">

    <?php
        // Status
        $status_class = "label";
        switch($model->status)
        {
            case OrderConstants::STATUS_ENABLED:
                $status_class .= " label-warning";
                break;
            case OrderConstants::STATUS_DISABLED:
                $status_class .= " label-danger";
                break;
            case OrderConstants::STATUS_FINISHED:
                $status_class .= " label-success";
                break;
        }

        // Customer
        $customer = $model->companyCustomer->customer;
        $customer_info = [
            $customer->getFullName(),
            $customer->phone,
        ];

        // Staff
        $staff_name = null;
        if ($model->staff) {
            $staff_name = $model->staff->getFullName();
        }
        $staff_info = [
            $model->divisionServices[0]->division->getTotalName(),
            $staff_name,
        ];

        // Type
        $type_class = "label";
        switch($model->type)
        {
            case OrderConstants::TYPE_MANUAL:
                $type_class .= " label-primary";
                break;
            case OrderConstants::TYPE_APPLICATION:
                $type_class .= " label-warning";
                break;
        }

        // Created user
        $created_info = "";
        switch($model->type)
        {
            case OrderConstants::TYPE_MANUAL:
                $created_info .= $model->createdUser->username . "<br/>";
                break;
            case OrderConstants::TYPE_APPLICATION:
                $created_info .= Yii::t('app', 'application') . "<br/>";
                break;
        }
        $date = new DateTime($model->created_time);
        $created_info .= $date->format("d.m, H:i");

        // Account
        $users = User::find()->where(['company_id' => $model->companyCustomer->company_id])->all();
        $users_list = \yii\helpers\ArrayHelper::getColumn($users, 'username');
    ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'key',
            [
                'attribute'=>'status',
                'format' => 'html',
                'value' => "<span class='{$status_class}'>" . OrderConstants::getStatuses()[$model->status] . "</span>",
            ],
            [
                'attribute' => 'datetime',
                'value' => (new DateTime($model->datetime))->format("d.m, H:i"),
            ],
            [
                'attribute' => 'company_customer_id',
                'format' => 'html',
                'value' => implode(",<br>", $customer_info),
            ],
            [
                'format' => 'html',
                'attribute' => 'staff_id',
                'value' => implode(",<br>", $staff_info),
            ],
            [
                'attribute' => 'created_time',
                'value' => (new DateTime($model->created_time))->format("d.m, H:i"),
            ],
            [
                'label' => Yii::t('app', 'Price currency'),
                'value' => number_format($model->price, 0, ',', ' '),
            ],
            [
                'attribute'=>'type',
                'format' => 'html',
                'value' => "<span class='{$type_class}'>" . OrderConstants::getTypes()[$model->type] . "</span>"
            ],
            'note',
            [
                'attribute' => 'created_user_id',
                'format' => 'html',
                'value' => $created_info,
            ],
            [
                'format' => 'html',
                'label' => Yii::t('app', 'Services'),
                'value' => $model->getServicesTitle("<br>"),
            ],
            [
                'label' => Yii::t('app', 'Division Administrator'),
                'value' => $model->divisionServices[0]->division->phone,
            ],
            [
                'label' => Yii::t('app', 'Accounts'),
                'format' => 'html',
                'value' => implode($users_list, ",<br/>"),
            ]
        ],
    ]) ?>

</div>
