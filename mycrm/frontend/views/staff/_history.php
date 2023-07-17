<?php

use core\helpers\order\OrderConstants;
use kartik\grid\GridView;
use yii\widgets\Pjax;

?>
<div id="events_history">
    <div class="events">
        <?php Pjax::begin(['timeout' => 5000]); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'striped' => true,
            'hover' => true,
            'columns' => [
                [
                    'class' => '\kartik\grid\DataColumn',
                    'attribute' => 'status',
                    'format' => 'html',
                    'value' => function ($data) {
                        $class = "label";
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
                        return "<span class='{$class}'>" . OrderConstants::getStatuses()[$data->status] . "</span>";
                    }
                ],
                [
                    'class' => '\kartik\grid\DataColumn',
                    'label' => Yii::t('app', 'Datetime'),
                    'value' => function ($data) {
                        $datetime = new DateTime($data->datetime);
                        return $datetime->format("d.m, H:i");
                    }
                ],
                [
                    'class' => '\kartik\grid\DataColumn',
                    'format' => 'html',
                    'label' => Yii::t('app', 'Services'),
                    'value' => function ($data) {
                        return $data->servicesTitle;
                    }
                ],
                [
                    'class' => '\kartik\grid\DataColumn',
                    'attribute' => 'company_customer_id',
                    'format' => 'html',
                    'value' => function ($data) {
                        $customer = $data->companyCustomer->customer;
                        $info = [
                            $customer->name . " " . $customer->lastname,
                            $customer->phone,
                        ];
                        return implode(",<br>", $info);
                    }
                ],
                [
                    'attribute' => 'price',
                    'label' => Yii::t('app', 'Price currency'),
                    'value' => function ($model) {
                        return number_format($model->price, 0, '.', ' ') . " " . Yii::t('app', 'Currency');
                    }
                ],
                [
                    'class' => '\kartik\grid\DataColumn',
                    'attribute' => 'type',
                    'format' => 'html',
                    'value' => function ($data) {
                        switch ($data->type) {
                            case OrderConstants::TYPE_MANUAL:
                                $title = OrderConstants::getTypes()[OrderConstants::TYPE_MANUAL];
                                return "<i class=\"icon sprite-customer_unknown_sex\" title='{$title}'></i>";
                            case OrderConstants::TYPE_APPLICATION:
                                $title = OrderConstants::getTypes()[OrderConstants::TYPE_APPLICATION];
                                return "<i class=\"icon sprite-settings_i18n\" title='{$title}'></i>";
                                break;
                            case OrderConstants::TYPE_SITE:
                                $title = OrderConstants::getTypes()[OrderConstants::TYPE_SITE];
                                return "<i class=\"icon sprite-settings_i18n\" title='{$title}'></i>";
                                break;
                            default:
                                return '';
                                break;
                        }
                    }
                ],
            ]
        ]);
        ?>
        <?php Pjax::end() ?>
    </div>
</div>