<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardCommentCategory */
/* @var $categories core\models\medCard\MedCardCommentCategory[] */
/* @var $serviceCategories core\models\ServiceCategory[] */
?>

<div class="comment-template-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'parent_id')
                ->dropDownList(
                    ArrayHelper::map($categories, 'id', 'name'),
                    ['class' => 'form-control', 'prompt' => Yii::t('app', 'Select')]
                ) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'service_category_id')
                ->dropDownList(
                    ArrayHelper::map($serviceCategories, 'id', 'name'),
                    ['class' => 'form-control', 'prompt' => Yii::t('app', 'Select')]
                ) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ?
                Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
