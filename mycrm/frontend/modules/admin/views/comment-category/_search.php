<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\search\CommentTemplateCategorySearch */
/* @var $form yii\widgets\ActiveForm */
/* @var $categories array */
?>

<div class="comment-template-category-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-md-6"><?= $form->field($model, 'name') ?></div>
        <div class="col-md-6">
            <?= $form->field($model, 'parent_id')
                ->dropDownList($categories, ['class' => 'form-control', 'prompt' => Yii::t('app', 'Select')]) ?>
        </div>
    </div>

    <div class="form-group pull-right">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
