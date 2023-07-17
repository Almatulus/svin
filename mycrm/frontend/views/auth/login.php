<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model core\forms\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\MaskedInput;

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container">
    <div class="logo">
        <img src="<?= Yii::getAlias('@web') ?>/image/logo.png"/>
    </div>
    <h1 class="form-heading text-center" style="padding: 1em 0 0.77em;"><?= Yii::t('app', 'Log in to cabinet') ?></h1>
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
    <div class="col-sm-4 col-sm-offset-2">
        <?= $form->field($model, 'username')->widget(MaskedInput::className(), [
            'mask' => '+7 999 999 99 99',
            'options' => [
                'autocomplete' => 'off',
                'class' => 'form-control',
                'placeholder' => Yii::t('app', 'Phone'),
            ]
        ])->label(false)/*->error(false)*/
        ; ?>
    </div>
    <div class="col-sm-4">
        <?= $form->field($model, 'password')
            ->passwordInput(['class' => 'form-control', 'placeholder' => Yii::t('app', 'Password')])
            ->label(false)/*->error(false)*/ ?>
    </div>
    <div class="col-sm-2">
        <?= Html::submitButton(Yii::t('app', 'Login'), [
            'id' => 'login-button',
            'class' => 'btn btn-primary  btn-flat',
            'name' => 'login-button'
        ]) ?>
    </div>
    <div class="col-sm-4 col-sm-offset-2">
    </div>
    <div class="col-sm-2">
        <?= ""//Html::a(Yii::t('app', 'Register'), ['register'])    ?>
    </div>
    <div class="col-sm-2">
        <?= Html::a(Yii::t('app', 'I forgot my password'), ['restore'], ['class' => 'pull-right']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<footer class="footer">
    <div class="container">
        <p class="text-muted text-center">Reone © <?= date('Y') ?></p>
    </div>
</footer>