<?php

use core\helpers\HtmlHelper as Html;
use core\models\customer\CompanyCustomer;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$gridColumns = [
    ['class' => 'yii\grid\CheckboxColumn'],
    'id',
    [
        'attribute' => 'name',
        'format'    => 'html',
        'value'     => function (CompanyCustomer $model) {
            $title = $model->customer->getFullName();
            if (Yii::$app->user->can("companyCustomerView")) {
                return Html::a($title, ['view', 'id' => $model->id]);
            } else {
                return $title;
            }
        }
    ],
    [
        'attribute' => 'phone',
        'label'     => Yii::t('app', 'Contacts'),
        'format'    => 'html',
        'value'     => function (CompanyCustomer $model) {
            return $model->customer->phone . "<br>" . $model->customer->email;
        }
    ],
    [
        'format'    => 'date',
        'attribute' => 'customer.birth_date',
    ],
    [
        'attribute' => 'customer.iin',
        'format'    => 'html',
        'value'     => function (CompanyCustomer $model) {
            return $model->customer->iin;
        }
    ],
    'medical_record_id',
    [
        'attribute' => 'lastVisit',
        'label'     => Yii::t('app', 'Last Visit Date'),
        'format'    => 'html',
        'value'     => function (CompanyCustomer $model) {
            $lastVisitDatetime = $model->getLastVisitDateTime();
            if ($lastVisitDatetime !== null) {
                return Yii::$app->formatter->asDate($lastVisitDatetime)
                       . "<br>"
                       . $model->lastOrder->staff->getFullName();
            }

            return "";
        }
    ],
    [
        'attribute' => 'moneySpent',
        'format'    => 'decimal',
        'hAlign'    => 'right',
        'value'     => function (CompanyCustomer $model) {
            return $model->revenue;
        }
    ],
    [
        'attribute' => 'discount',
        'value'     => function (CompanyCustomer $model) {
            return $model->discount . ' %';
        },
        'hAlign'    => 'right',
    ],
    [
        'label' => Yii::t('app', 'Gender'),
        'value' => function (CompanyCustomer $model) {
            return $model->customer->getGenderName();
        }
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'id'           => 'customers',
    'options'      => [
        'class' => 'customers-table',
    ],
    'toolbar'      => [
        '{export}',
        '{toggleData}'
    ],

    'columns'        => $gridColumns,
    'responsiveWrap' => false,
    'summary'        => Html::getSummary(),
    'pager'          => [
        'firstPageLabel' => Yii::t('app', 'First'),
        'lastPageLabel'  => Yii::t('app', 'Last'),
        'pageCssClass'   => 'js-customers-search'
    ],
]);
