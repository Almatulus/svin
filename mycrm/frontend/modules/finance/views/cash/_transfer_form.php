<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $modelForm $model core\forms\finance\CashTransferForm */
/* @var $model core\models\finance\CompanyCash */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-cash-transfer-form">

    <?php $form = ActiveForm::begin(['action' => ['transfer', 'id' => $model->id]]); ?>

    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($modelForm, 'cash_id')->dropDownList($cashes) ?>

            <?= $form->field($modelForm, 'amount')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box-buttons pull-right">
                <button class="btn btn-primary" name="button" type="submit">Отправить</button>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
