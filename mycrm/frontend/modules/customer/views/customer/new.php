<?php
use core\helpers\customer\CustomerHelper;
use core\helpers\GenderHelper;
use yii\widgets\ActiveForm;

?>

<div class='inner'>
    <?php $form = ActiveForm::begin(['id' => 'customer-form', 'options' => ['class' => 'simple_form']]); ?>

    <ol>
        <li class="control-group string optional customer_name">
                <div class="controls">
                    <?= $form->field($model, 'name')->textInput(['class' => 'string options']) ?>
                </div>
            </li>
            <li class="control-group string optional customer_lastname">
                <div class="controls">
                     <?= $form->field($model, 'lastname')->textInput(['class' => 'string options']) ?>
                </div>
            </li>
            <li class="control-group email optional customer_email">
                <div class="controls">
                    <?= $form->field($model, 'email')->textInput(['class' => 'string options']) ?>
                </div>
            </li>
            <li class="control-group tel optional customer_phone">
                <div class="controls">
                    <?= $form->field($model, 'phone', ['options' => ['class' => 'reone-phone-input']])->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '+7 999 999 99 99',
                    ]) ?>
                </div>
            </li>
            <li class="control-group string optional customer_gender">
                <div class="controls">
                    <?= $form->field($model, 'gender')->dropDownList(GenderHelper::getGenders()) ?>
                </div>
            </li>
    </ol>
    <?php $form->end(); ?>
</div>