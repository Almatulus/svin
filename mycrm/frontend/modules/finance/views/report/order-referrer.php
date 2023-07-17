<?php

use core\helpers\HtmlHelper as Html;
use core\helpers\OrderHelper;
use core\models\company\Referrer;
use frontend\modules\finance\search\OrderReferrerSearch;
use core\models\order\Order;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel OrderReferrerSearch */

$this->title                   = Yii::t('app', 'Referrer report');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label'    => $this->title
];
?>

<div class="report-order-referrer">
    <?php
    $form = ActiveForm::begin([
        'action' => ['referrer'],
        'method' => 'get'
    ]); ?>
    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($searchModel, 'from')
                     ->widget(DatePicker::className(), [
                         'type'          => DatePicker::TYPE_INPUT,
                         'pluginOptions' => [
                             'autoclose' => true,
                             'format'    => 'yyyy-mm-dd'
                         ]
                     ]) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($searchModel, 'to')
                     ->widget(DatePicker::className(), [
                         'type'          => DatePicker::TYPE_INPUT,
                         'pluginOptions' => [
                             'autoclose' => true,
                             'format'    => 'yyyy-mm-dd'
                         ]
                     ]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($searchModel, 'referrer_id')
                     ->widget(Select2::className(), [
                         'options'       => [
                             'placeholder' =>
                                 Yii::t('app', 'Enter the name or select from list')
                         ],
                         'data'          => Referrer::map(),
                         'size'          => 'sm',
                         'pluginOptions' => [
                             'width'      => '240px',
                             'allowClear' => true
                         ]
                     ]) ?>
        </div>
    </div>
    <div class="row column_row">
        <div class="col-sm-12">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'showFooter'   => true,
                'summary'      => Html::getSummary(),
                'columns'      => [
                    [
                        'label'  => Yii::t('app', 'Datetime'),
                        'format' => 'datetime',
                        'value'  => function (Order $model) {
                            return $model->datetime;
                        }
                    ],
                    [
                        'label' => Yii::t('app', 'Customer'),
                        'value' => function (Order $model) {
                            return $model->companyCustomer->customer->getFullName();
                        }
                    ],
                    [
                        'label' => Yii::t('app', 'Staff'),
                        'value' => function (Order $model) {
                            return $model->staff->getFullName();
                        }
                    ],
                    [
                        'format' => 'html',
                        'label'  => Yii::t('app', 'Services'),
                        'value'  => function (Order $model) {
                            return $model->getServicesTitle("<hr>");
                        }
                    ],
                    [
                        'label' => Yii::t('app', 'Referrer'),
                        'value' => function (Order $model) {
                            return $model->referrer->name;
                        }
                    ],
                    [
                        'label' => Yii::t('app', 'Customer Source'),
                        'value' => function (Order $model) {
                            $source = $model->companyCustomer->source;

                            return $source !== null ? $source->name : null;
                        }
                    ],
                    [
                        'label'  => Yii::t('app', 'Paid'),
                        'format' => 'decimal',
                        'value'  => function (Order $model) {
                            return $model->getIncomeCash();
                        },
                        'hAlign' => 'right',
                        'footer' => Yii::$app->formatter->asDecimal(
                            OrderHelper::getTotalPaidSum($dataProvider->getModels())
                        ),
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?= Html::submitButton(
                '<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export fetched to Excel'), [
                'class' => 'btn btn-default',
                'value' => 'export',
                'name'  => 'action'
            ]) ?>
            <?= Html::a(
                '<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export all to Excel'),
                ['export'],
                ['class' => 'btn btn-default']
            ) ?>
        </div>
    </div>
    <?php $form->end(); ?>
</div>
