<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-category-form">

    <?php $form = ActiveForm::begin(['id' => 'customer-category-form', 'options' => ['class' => 'simple_form']]); ?>

    <ol>
        <li class="control-group string optional customer-category_name">
            <div class="controls">
                <?= $form->field($model, 'name')->textInput(['class' => 'string options']) ?>
            </div>
        </li>
        <li class="control-group string optional customer-category_discount">
            <div class="controls">
                <?= $form->field($model, 'discount', [
                    'template' => "{label}\n{input} %\n{hint}\n{error}"
                ])->textInput(['style' => 'width:44px;'])?>
            </div>
        </li>
        <li class="control-group string optional customer-category_color">
            <div class="controls">
                <?= $form->field($model, 'color')
                    ->textInput(['maxlength' => true,
                                 'type' => 'color',
                                 'style' => 'width:44px; padding:1px 2px;']) ?>
            </div>
        </li>
    </ol>

    <?php ActiveForm::end(); ?>

</div>
