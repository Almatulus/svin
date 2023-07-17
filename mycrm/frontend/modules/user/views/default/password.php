<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\user\User */

$this->title = "Изменить пароль";
?>
<div class="user-update">

    <div class="user-form">

        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'options' => ['tag' => 'li', 'class' => 'control-group'],
                'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
                'wrapperOptions' => ['class' => 'controls']
            ],
            'options' => ['class' => 'simple_form']
        ]); ?>

        <ol>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'new_password')->passwordInput() ?>
            <?= $form->field($model, 'password_repeat')->passwordInput() ?>
        </ol>

        <div class="form-group">
            <?= Html::submitButton(
                Yii::t('app', 'Save'),
                ['class' => 'btn btn-primary', 'name' => 'change-password']
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
