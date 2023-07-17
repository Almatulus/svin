<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model core\forms\LoginForm */

use himiklab\yii2\recaptcha\ReCaptcha;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title                   = Yii::t('app', 'Restore');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="container">
    <div class="logo">
        <a href="/">
            <img src="<?= Yii::getAlias('@web') ?>/image/logo.png"/>
        </a>
    </div>
    <h1 class="form-heading text-center" style="padding: 1em 0 0.77em;">Восстановление пароля</h1>
    <?php $form = ActiveForm::begin(['id' => 'restore-form']); ?>
    <div class="col-sm-12">
        <div class="restore-form text-center">
            <div id="phone-wrap">
                <?= $form->field($model, 'phone', [
                    'options' => [
                        'class' => 'reone-phone-input',
                        'style' => 'max-width: 304px;'
                    ],
                ])->widget(\yii\widgets\MaskedInput::className(), [
                    'mask' => '+7 999 999 99 99',
                    'clientOptions' => [
                        'clearIncomplete' => true
                    ],
                    'options' => ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Enter Phone')]
                ])->label(false) ?>

                <?= $form->field($model, 'reCaptcha')
                         ->widget(ReCaptcha::className())
                         ->label(false) ?>
            </div>

            <div id="key-wrap" class="col-sm-4 col-sm-offset-4" style="display:none">
                <?= $form->field($model, 'code', [
                    'options' => ['class' => 'reone-key-input']
                ])->passwordInput(['class' => 'form-control', 'placeholder' => Yii::t('app', 'Enter Code')])->label(false);
                ?>
            </div>

            <div id="pass-wrap" class="col-sm-4 col-sm-offset-4" style="display:none">
                <?= $form->field($model, 'password', [
                    'options' => ['class' => 'reone-key-input pass']
                ])->passwordInput(['class' => 'form-control', 'id' => 'pass', 'placeholder' => Yii::t('app', 'Password')])->label(false); ?>
                <?= $form->field($model, 'repassword', [
                    'options' => ['class' => 'reone-key-input repass'],
                ])->passwordInput(['class' => 'form-control', 'id' => 'repass', 'placeholder' => Yii::t('app', 'Confirm Password')])->label(false); ?>
            </div>

            <div class="col-sm-12">
                <?= Html::button(Yii::t('app', 'Restore'), [
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