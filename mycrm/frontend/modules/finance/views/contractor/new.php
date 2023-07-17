<?php

use core\models\division\Division;
use core\models\finance\CompanyContractor;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyContractor */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-contractor-form">

    <?php $form = ActiveForm::begin([
        'id' => 'companycontractor-form',
        'options' => ['class' => 'simple_form new_stock_entity'],
        'fieldConfig' => ['options' => ['class' => '']],
    ]); ?>

    <ol>
        <li class="control-group string optional contractor_type">
            <div class="controls">
                <?= $form->field($model, 'type')->dropDownList(CompanyContractor::getTypeLabels()) ?>
            </div>
        </li>
        <li class="control-group string optional contractor_name">
            <div class="controls">
                <?= $form->field($model, 'name')->textInput(['class' => 'string options', 'maxlength' => true]) ?>
            </div>
        </li>
        <li class="control-group string optional contractor_division_id">
            <div class="controls">
                <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList()) ?>
            </div>
        </li>
        <li class="control-group string optional contractor_iin">
            <div class="controls">
                <?= $form->field($model, 'iin')->textInput(['class' => 'string options', 'maxlength' => true]) ?>
            </div>
        </li>
        <li class="control-group string optional contractor_kpp">
            <div class="controls">
                <?= $form->field($model, 'kpp')->textInput(['class' => 'string options', 'maxlength' => true]) ?>
            </div>
        </li>
        <li class="control-group string optional contractor_contacts">
            <div class="controls">
                <?= $form->field($model, 'contacts')->textInput(['class' => 'string options', 'maxlength' => true]) ?>
            </div>
        </li>
        <li class="control-group tel optional contractor_phone">
            <div class="controls">
                <?= $form->field($model, 'phone', ['options' => ['class' => 'reone-phone-input']])->widget(\yii\widgets\MaskedInput::className(), [
                    'mask' => '+7 999 999 99 99',
                ]) ?>
            </div>
        </li>
        <li class="control-group email optional contractor_email">
            <div class="controls">
                <?= $form->field($model, 'email')->textInput(['class' => 'string options']) ?>
            </div>
        </li>
        <li class="control-group string optional contractor_contacts">
            <div class="controls">
                <?= $form->field($model, 'address')->textInput(['class' => 'string options', 'maxlength' => true]) ?>
            </div>
        </li>
        <li class="control-group string optional">
            <div class="controls">
                <?= $form->field($model, 'comments')->textarea(['rows' => 6, 'class' => 'string options', 'maxlength' => true]) ?>
            </div>
        </li>
    </ol>

    <?php ActiveForm::end(); ?>

</div>
