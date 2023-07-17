<?php

/**
 * @var $model core\models\order\Order
 * @var $this  yii\web\View
 */
use core\helpers\OrderHistoryHelper;
use core\helpers\order\OrderConstants;
use core\models\order\OrderHistory;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => $model->getOrderHistory(),
    'sort'  => ['defaultOrder' => ['created_time' => SORT_ASC]]
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-condensed'],
    'columns'      => [
        'created_time:datetime',
        [
            'attribute' => 'action',
            'value'     => function (OrderHistory $model) {
                return OrderHistoryHelper::getActionLabel($model->action);
            }
        ],
        'datetime:datetime',
        [
            'attribute' => 'status',
            'value'     => function (OrderHistory $model) {
                return OrderConstants::getStatuses()[$model->status];
            }
        ],
        'acting_user',
    ],
]);