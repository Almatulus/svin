<?php
use yii\widgets\ActiveForm;

?>

<div class='inner'>
    <?php $form = ActiveForm::begin(['id' => 'manufacturer-form', 'options' => ['class' => 'simple_form']]); ?>

    <ol>
        <li class="control-group string required manufacturer_name">
            <div class='controls'>
                <?= $form->field($model, 'name', [
                    'options' => ['class' => ''],
                    'template' => "{label}\n{input}\n{error}",
                    'inputOptions' => ['style' => 'width:240px'],
                    'errorOptions' => ['class' => 'help-block', 'style' => 'margin:0']
                ]); ?>
            </div>
        </li>
    </ol>
    <?php $form->end(); ?>
</div>