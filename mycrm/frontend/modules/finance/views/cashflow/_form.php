<?php

use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyContractor;
use core\models\finance\CompanyCostItem;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \core\forms\finance\CashflowForm|\core\forms\finance\CashflowUpdateForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $type integer */

$paymentList = Division::getAllPayments();

?>
<style>
    .datetimepicker {
        z-index: 1000015 !important;
    }
</style>

<div class="company-cashflow-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'options'        => ['tag' => 'li', 'class' => 'control-group'],
            'template'       => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
            'wrapperOptions' => ['class' => 'controls'],
        ],
        'options'     => ['class' => 'simple_form']
    ]); ?>
    <ol>
        <?= $form->field($model, 'date')->widget(\kartik\datetime\DateTimePicker::className(), [
            'type'          => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => 'yyyy-mm-dd hh:ii'
            ]
        ])?>
        <?php if (!$model->getCashflow() || $model->getCashflow()->isEditable()) { ?>
            <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList()) ?>
            <?= $form->field($model, 'cost_item_id')->widget(Select2::className(), [
                'data'          => CompanyCostItem::map($type),
                'options'       => ['multiple' => false, 'placeholder' => Yii::t('app', 'Select Cost Item')],
                'pluginOptions' => [
                    'allowClear' => true,
                    'language'   => 'ru',
                    'width'      => '240px',
                ],
                'size'          => 'sm',
                'showToggleAll' => false,
            ]) ?>
            <?= $form->field($model, 'cash_id')->dropDownList(CompanyCash::map()) ?>
            <?= $form->field($model, 'contractor_id')->widget(Select2::className(), [
                'data'          => CompanyContractor::map(),
                'options'       => ['multiple' => false, 'placeholder' => Yii::t('app', 'Select Contractor')],
                'pluginOptions' => [
                    'allowClear' => true,
                    'language'   => 'ru',
                    'width'      => '240px',
                ],
                'size'          => 'sm',
                'showToggleAll' => false,
            ]) ?>
            <?= $form->field($model, 'value')->textInput() ?>
            <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>
        <?php } ?>
        <?= $form->field($model, 'payments')->widget(MultipleInput::class, [
            'min'     => sizeof($model->payments),
            'max'     => sizeof($model->payments),
            'columns' => [
                [
                    'name'  => 'name',
                    'title' => Yii::t('app', "Name"),
                    'type'  => 'static',
                    'value' => function ($data) use ($paymentList) {
                        if (isset($data['payment_id'])) {
                            return Html::tag('span', $paymentList[$data['payment_id']]);
                        }
                        return '';
                    }
                ],
                [
                    'name' => 'payment_id',
                    'type' => MultipleInputColumn::TYPE_HIDDEN_INPUT
                ],
                [
                    'name'         => 'value',
                    'defaultValue' => 0,
                    'title'        => Yii::t('app', "Sum"),
                    'enableError'  => true,
                ]
            ]
        ]) ?>
    </ol>
    <div class="box-buttons">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
        <div class="pull-right">
            <?= Html::a("отмена", Yii::$app->request->referrer) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>