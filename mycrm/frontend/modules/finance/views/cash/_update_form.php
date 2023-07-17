<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model $model core\models\finance\CompanyCash */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-cash-form">

    <?php $form = ActiveForm::begin(['action' => ['edit', 'id' => $model->id]]); ?>

    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'init_money')->textInput() ?>

            <?= $form->field($model, 'name')->textInput() ?>

            <div class="cash-register-buttons-box">
                <?= $form->field($model, 'comments', ['options' => ['class' => '']])->textarea(['placeholder' => 'Комментарии', 'rows' => 20]) ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box-buttons pull-right">
                <button class="btn btn-primary" name="button" type="submit">Сохранить</button>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
