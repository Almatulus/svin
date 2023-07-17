<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model core\forms\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title                   = Yii::t('app', 'Registration');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="container">
    <div class="logo">
        <img src="<?= Yii::getAlias('@web') ?>/image/logo.png"/>
    </div>
    <h1 class="form-heading text-center" style="padding: 1em 0 0.77em;"><?= Yii::t('app', 'Registration'); ?></h1>
    <?php $form = ActiveForm::begin(['id' => 'register-form']); ?>
    <div class="col-sm-12">
        <div class="register-form text-center">

            <div id="key-wrap" class="col-sm-4 col-sm-offset-4">
                <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(), [
                    'mask' => '+7 999 999 99 99',
                    'options' => ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Phone')]
                ])->label(false); ?>

                <?= $form->field($model, 'code', [
                    'options' => ['class' => 'reone-key-input']
                ])->passwordInput(['class' => 'form-control', 'placeholder' => Yii::t('app', 'Enter Code')])->label(false);
                ?>
            </div>

            <div class="col-sm-12">
                <?= Html::submitButton(Yii::t('app', 'Register'), [
                    'id' => 'btn-restore',
                    'class' => 'btn btn-primary btn-flat',
                    'onClick' => 'cur.forgotFormSend()'
                ]) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<footer class="footer">
    <div class="container">
        <p class="text-muted text-center">Reone © <?= date('Y') ?></p>
    </div>
</footer>