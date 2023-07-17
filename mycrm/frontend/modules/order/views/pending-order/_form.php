<?php

use core\models\division\Division;
use core\models\Staff;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \core\forms\order\PendingOrderForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pending-order-form">

    <?php $form = ActiveForm::begin([
        'id'     => 'pending-order-form',
        'action' => '/order/pending-order/create',
        // 'enableAjaxValidation' => true,
    ]); ?>

    <?= Html::hiddenInput('PendingOrder[id]', null, ['id' => 'pendingorder-id']) ?>

    <?= $form->field($model, 'company_customer_id')->hiddenInput()->label(false)->error(false) ?>

    <?= $form->field($model, 'customer_name')->textInput() ?>

    <?= $form->field($model, 'customer_phone')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '+7 999 999 99 99',
    ]) ?>

    <?= $form->field($model, 'date')->widget(DatePicker::className(), [
        'type'          => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'autoclose' => true,
            'format'    => 'yyyy-mm-dd'
        ]
    ]) ?>

    <?= $form->field($model, 'staff_id')
        ->dropDownList(
            ArrayHelper::map(
                Staff::find()->company(false)
                    ->permitted()
                    ->enabled()
                    ->timetableVisible()
                    ->all(), 'id', 'name'
            ), [
            'prompt' => Yii::t('app', 'Select staff'),
        ]) ?>

    <?= $form->field($model, 'division_id')
        ->dropDownList(Division::getOwnCompanyDivisionsList(), [
            'prompt' => Yii::t('app', 'Select division'),
        ]) ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        <?= Html::button(Yii::t('app', 'Delete'), [
            'class' => 'btn btn-danger pull-right js-delete-pending-order'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
