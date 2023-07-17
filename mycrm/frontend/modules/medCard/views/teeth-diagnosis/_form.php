<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardToothDiagnosis */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="med-card-teeth-diagnosis-form">

    <?php $form = ActiveForm::begin(['options' => ['class' => 'simple_form']]); ?>

    <ol>
        <li class="control-group string optional med-card-teeth-diagnosis_name">
            <div class="controls">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            </div>
        </li>
        <li class="control-group string optional med-card-teeth-diagnosis_abbreviation">
            <div class="controls">
                <?= $form->field($model, 'abbreviation')->textInput(['maxlength' => true]) ?>
            </div>
        </li>
        <li class="control-group string optional med-card-teeth-diagnosis_color">
            <div class="controls">
                <?= $form->field($model, 'color')
                         ->textInput(['maxlength' => true,
                                      'type' => 'color',
                                      'style' => 'width:44px; padding:1px 2px;']) ?>
            </div>
        </li>
    </ol>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
