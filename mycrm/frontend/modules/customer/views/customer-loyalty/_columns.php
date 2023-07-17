<?php

use core\helpers\customer\CustomerLoyaltyHelper;
use core\models\customer\CustomerLoyalty;
use yii\helpers\Url;

return [
    [
        'width' => '75px',
        'value' => function () {
            return '';
        },
    ],
    [
        'attribute'         => 'mode',
        'format'            => 'raw',
        'value' => function (CustomerLoyalty $model) {
            $mode_label = CustomerLoyaltyHelper::getModeLabel($model->mode);

            return "<h3><bold>{$mode_label}</bold></h3>";
        },
        'group'             => true,
        // enable grouping,
        'groupedRow'        => true,
        // move grouped column to a single grouped row
        'groupOddCssClass'  => 'header-row',
        // configure odd group cell css class
        'groupEvenCssClass' => 'header-row',
        // configure even group cell css class
    ],
    [
        'label'      => Yii::t('app', 'Condition'),
        'value'      => function (CustomerLoyalty $model) {
            return CustomerLoyaltyHelper::getEventLabel($model->event);
        },
        // 'group'=>true,  // enable grouping
        'subGroupOf' => 1,
        // supplier column index is the parent group
        //        'groupOddCssClass'=>'',  // configure odd group cell css class
        //        'groupEvenCssClass'=>'', // configure even group cell css class
    ],
    [
        'label' => Yii::t('app', 'Amount'),
        'value' => function (CustomerLoyalty $model, $key, $index, $widget) {
            $result = number_format($model->amount);
            if ($model->event == CustomerLoyalty::EVENT_MONEY) {
                $result .= ' ' . Yii::t('app', 'Currency');
            }
            if ($model->event == CustomerLoyalty::EVENT_VISIT) {
                $result .= '';
            }
            if ($model->event == CustomerLoyalty::EVENT_DAY) {
                $result .= ' дней';
            }

            return $result;
        },
    ],
    [
        'format' => 'raw',
        'value'  => function ($model) {
            return '<i class="fa fa-long-arrow-right"></i>';
        }
    ],
    [
        'value' => function (CustomerLoyalty $model, $key, $index, $widget) {
            if ($model->mode == CustomerLoyalty::MODE_ADD_DISCOUNT) {
                return $model->discount . ' %';
            }
            if ($model->mode == CustomerLoyalty::MODE_REMOVE_DISCOUNT) {
                return '';
            }
            if ($model->isCategoryMode()) {
                return $model->category->name ?? "";
            }
            return '';
        },
    ],
    [
        'class'         => 'kartik\grid\ActionColumn',
        'template'      => '{update} {delete}',
        'dropdown'      => false,
        'vAlign'        => 'middle',
        'urlCreator'    => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'updateOptions' => [
            'role'        => 'modal-remote',
            'title'       => 'Update',
            'class'       => 'btn btn-xs btn-default',
            'data-toggle' => 'tooltip'
        ],
        'deleteOptions' => [
            'role'                 => 'modal-remote',
            'title'                => 'Delete',
            'class'                => 'btn btn-xs btn-danger',
            'data-confirm'         => false,
            'data-method'          => false,// for overide yii data api
            'data-request-method'  => 'post',
            'data-toggle'          => 'tooltip',
            'data-confirm-title'   => Yii::t('app', 'Deleting'),
            'data-confirm-message' => Yii::t('yii',
                'Are you sure you want to delete this item?')
        ],
    ],
];