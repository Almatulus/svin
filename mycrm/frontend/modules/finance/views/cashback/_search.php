<?php

use core\models\customer\CompanyCustomer;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\finance\search\CashbackSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cashback-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, 'from', [
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'to', [
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-3"><?= $form->field($model, 'minAmount') ?></div>
        <div class="col-sm-3"><?= $form->field($model, 'maxAmount') ?></div>
        <div class="col-sm-2">
            <?= $form->field($model, 'type')->dropDownList(\core\helpers\company\CashbackHelper::getTypes(), [
                'prompt' => Yii::t('app', 'Select')
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?php $companyCustomer = $model->company_customer_id
                ? CompanyCustomer::findOne($model->company_customer_id)
                : null ?>
            <?= $form->field($model, "company_customer_id")->widget(Select2::class, [
                    'initValueText' => $companyCustomer ? $companyCustomer->customer->getFullName() . " " . $companyCustomer->customer->phone : null,
                    'options'       => ['placeholder' => Yii::t('app', 'Select customer')],
                    'pluginOptions' => [
                        'allowClear'         => false,
                        'minimumInputLength' => 3,
                        'ajax'               => [
                            'url'      => ['/customer/customer/user-list'],
                            'dataType' => 'json',
                            'data'     => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                        'templateResult'     => new JsExpression('function(user) { return user.text; }'),
                        'templateSelection'  => new JsExpression('function (user) { return user.text; }'),
                    ],
                    'size'          => 'sm'
                ]
            );
            ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
