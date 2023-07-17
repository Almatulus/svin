<?php

use core\models\customer\CustomerRequest;
use kartik\date\DatePicker;
use rmrevin\yii\fontawesome\FA;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $form yii\widgets\ActiveForm */
/* @var $smsTotal integer */
/* @var $priceTotal integer */

$this->title = Yii::t('app', 'Customer Requests');
$this->params['breadcrumbs'][]    = ['template' => '<li class="active"><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => $this->title];
?>
<div class="customer-request-index">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-4">
        <?= $form->field($searchModel, 'from',[
            'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From date').'</span>{input}</div>',
        ])->widget(DatePicker::className(), [
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
        ])->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd'
            ]
        ]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($searchModel, 'type')
            ->label(false)
            ->dropDownList(CustomerRequest::getTypeLabels(), ['prompt' => Yii::t('app','Select types')]) ?>
    </div>

    <div class="col-md-12">
        <?= $form->field($searchModel, 'phone', [
            'template' => '<div class="input-group"><span class="input-group-addon">'.FA::icon('phone').'</span>{input}</div>',
        ])->textInput([
            'placeholder' => Yii::t('app', 'Type telephone number'),
        ]); ?>
    </div>

    <div class="col-md-12 text-right">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']); ?>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="col-md-12">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'showFooter' => true,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'created_time',
                    'format' => 'datetime',
                ],
                [
                    'label' => Yii::t('app','Phone'),
                    'value' => function(CustomerRequest $request) {
                        return $request->receiver_phone;
                    }
                ],
                [
                    'attribute' => 'code',
                    'contentOptions' => ['style' =>
                        'max-width: 400px; white-space: normal;'],
                    'value' => function ($data)
                    {
                        return $data->code;
                    }
                ],
                [
                    'attribute' => 'type',
                    'value' => function(CustomerRequest $request) {
                        return CustomerRequest::getTypeLabels()[$request->type];
                    }
                ],
                [
                    'attribute' => 'smsCount',
                    'value' => function(CustomerRequest $request) {
                        return $request->getSmsCount();
                    },
                    'footer' => $smsTotal,
                ],
                [
                    'attribute' => 'price',
                    'format' => 'decimal',
                    'value' => function(CustomerRequest $request) {
                        return $request->getPrice();
                    },
                    'footer' => Yii::$app->formatter->asDecimal($priceTotal),
                ],
                [
                    'attribute' => 'smscStatus',
                    'value' => function(CustomerRequest $request) {
                        return $request->getSmscStatus();
                    }
                ],
            ],
        ]); ?>
    </div>

</div>
