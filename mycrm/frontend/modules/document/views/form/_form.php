<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentForm */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="document-form-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'doc_path')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'has_dental_card')->checkbox() ?>
    <?= $form->field($model, 'has_services')->checkbox() ?>
    <?= $form->field($model, 'enabled')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'),
            ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
