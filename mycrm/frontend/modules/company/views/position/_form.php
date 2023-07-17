<?php

use core\models\medCard\MedCardCommentCategory;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\company\CompanyPosition */
/* @var $documentFormList array */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-position-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'categories')
        ->widget(Select2::className(), [
            'data'    => MedCardCommentCategory::mappedList(),
            'options' => [
                'multiple' => true
            ],
        ]) ?>

    <?= $form->field($model, 'documentForms')
        ->widget(Select2::className(), [
            'data'    => $documentFormList,
            'options' => [
                'multiple' => true
            ],
        ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'),
            ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
