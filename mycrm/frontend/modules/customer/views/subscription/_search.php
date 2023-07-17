<?php

use core\models\customer\CustomerSubscription;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\customer\search\SubscriptionSearch */
/* @var $form yii\widgets\ActiveForm */

$datePickerOptions = [
    'options' => ['placeholder' => Yii::t('app', 'Select date')],
    'type' => DatePicker::TYPE_INPUT,
    'pluginOptions' => [
        'autoclose' => true,
        'format' => 'yyyy-mm-dd',
    ]
];
$startDateOptions = [
    'template' => '{label}<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'From date') . '</span>{input}</div>'
];
$endDateOptions = [
    'template' => '{label}<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'To date') . '</span>{input}</div>'
];
?>
<div class="customer-subscription-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row" style="display: flex; align-items: flex-end; flex-wrap: wrap">
        <div class="col-sm-2">
            <?= $form->field($model, 'purchased_start', $startDateOptions)->widget(DatePicker::className(), $datePickerOptions)->label(Yii::t('app', 'Purchased')) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'purchased_end', $endDateOptions)->widget(DatePicker::className(), $datePickerOptions)->label(false) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'first_visit_start', $startDateOptions)->widget(DatePicker::className(), $datePickerOptions)->label(Yii::t('app', 'First visit')) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'first_visit_end', $endDateOptions)->widget(DatePicker::className(), $datePickerOptions)->label(false) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'expiry_date_start', $startDateOptions)->widget(DatePicker::className(), $datePickerOptions)->label(Yii::t('app', 'Expiry date')) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'expiry_date_end', $endDateOptions)->widget(DatePicker::className(), $datePickerOptions)->label(false) ?>
        </div>
    </div>
    <div class="row" style="display: flex; align-items: flex-end; flex-wrap: wrap">
        <div class="col-sm-1">
            <?= $form->field($model, 'key')->label(Yii::t('app', 'Number')) ?>
        </div>
        <div class="col-sm-1">
            <?= $form->field($model, 'quantity') ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'company_customer_id')->widget(Select2::classname(), [
                'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'Enter the name or phone number')],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => ['/customer/customer/user-list'],
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(user) { return user.text; }'),
                    'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                    'width' => '100%'
                ],
                'size' => 'sm'
            ]) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'expiry_date_start', $startDateOptions)->label(Yii::t('app', 'Price')) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'expiry_date_end', $endDateOptions)->label(false) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'status')->dropDownList(CustomerSubscription::getStatuses(), [
                'prompt' => 'Выберите статус'
            ]) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'type')->dropDownList(CustomerSubscription::getTypes(), [
                'prompt' => 'Выберите тип'
            ]) ?>
        </div>
    </div>
    <div class="column_row row buttons-row">
        <div class="col-sm-12 right-buttons">
            <?= Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary pull-right']) ?>
            <?= Html::a(Yii::t('app', 'Add season ticket'), ['create'], ['class' => 'btn btn-primary pull-right right_space']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
