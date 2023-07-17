<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $company core\models\company\Company */

$this->title = Yii::t('app', 'Pay account');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => $company->name
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Payment');
?>
<div class="company-payment">
    <div class="row">
        <div class="col-sm-8">
        </div>

        <div class="col-sm-4">
            <?= \yii\widgets\DetailView::widget([
                'model' => $company,
                'attributes' => [
                    [
                        'attribute' => 'tariff_id',
                        'value'     => $company->tariff->name ?? null,
                    ],
                    [
                        'attribute' => 'balance',
                        'format' => 'decimal',
                        'value' => $company->getBalance(),
                    ],
                    [
                        'label' => 'SMS',
                        'value' => $company->getSmsLimit() . ' SMS осталось',
                    ],
                    [
                        'label'  => Yii::t('app', 'Last Payment'),
                        'format' => 'date',
                        'value'  => $company->lastTariffPayment->start_date ?? null
                    ]
                ],
                'options' => ['class' => 'table table-striped table-bordered data_table no_hover']
            ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'created_time:datetime',
                    [
                        'attribute' => 'value',
                        'format' => 'decimal',
                    ],
                    'description',
                    [
                        'class'=>'\kartik\grid\DataColumn',
                        'format' => 'html',
                        'value' => function ($model){
                            /* @var \core\models\CompanyPaymentLog $model */
                            $class = "label";
                            $statusName = Yii::t('app', 'Approved');
                            if ($model->confirmed_time == null) {
                                $class .= " label-danger";
                                $statusName = Yii::t('app', 'Not approved');
                            } else {
                                $class .= " label-success";
                            }
                            return "<span class='{$class}'>" . $statusName . "</span>";
                        }
                    ],
                    [
                        'format' => 'html',
                        'value' => function($model) {
                            if ($model->confirmed_time == null) {
                                return Html::a(Yii::t('app', 'Proceed to payment'), ['wallet-one', 's' => $model->code]);
                            }
                            return '';
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
