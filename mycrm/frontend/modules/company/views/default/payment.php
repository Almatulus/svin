<?php

use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $company core\models\company\Company */
/* @var $searchModel \frontend\search\CompanyPaymentLogSearch */

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
            <?php $form = ActiveForm::begin([
                'action' => 'payment',
                'method' => 'get',
            ]); ?>

            <div class="col-md-4">
                <?= $form->field($searchModel, 'from',[
                    'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From date').'</span>{input}</div>',
                ])->widget(DatePicker::class, [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ])?>
            </div>
            <div class="col-md-4">
                <?= $form->field($searchModel, 'to',[
                    'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','To date').'</span>{input}</div>',
                ])->widget(DatePicker::class, [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ]) ?>
            </div>

            <div class="col-md-4">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']); ?>
            </div>

            <?php ActiveForm::end(); ?>
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
                    [
                        'attribute' => 'description',
                        'format' => 'html',
                        'value' => function ($model){
                            $description = $model->description;
                            if ($model->customer_request_id){
                                $description .= Html::tag('br') .
                                                Html::tag('p',
                                                    $model->customerRequest->code,
                                                    ['class' => 'text-muted small']
                                                );
                            }
                            return $description;
                        }
                    ],
                    [
                        'class'=>'\kartik\grid\DataColumn',
                        'format' => 'html',
                        'value' => function ($model){
                            /* @var \core\models\CompanyPaymentLog $model */
                            $class = "label";
                            $statusName = Yii::t('app', 'Approved');
                            if ( ! $model->isApproved()) {
                                $class .= " label-danger";
                                $statusName = Yii::t('app', 'Not approved');
                            } else {
                                $class .= " label-success";
                            }
                            return "<span class='{$class}'>" . $statusName . "</span>";
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
