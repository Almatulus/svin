<?php

use core\models\document\DocumentForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentFormGroup */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="document-form-group-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'order')->textInput() ?>

    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'document_form_id')->dropDownList(
        ArrayHelper::map(DocumentForm::find()->select(['id', 'name'])->asArray()->all(), "id", "name"),
        ['prompt' => Yii::t('app', 'Select document form')]
    ) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
