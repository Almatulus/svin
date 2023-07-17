<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerSource */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-source-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput() ?>

    <div class="form-group">
        <button class="btn btn-primary" type="submit">
            <span class="icon sprite-add_customer_save"></span>Сохранить
        </button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
