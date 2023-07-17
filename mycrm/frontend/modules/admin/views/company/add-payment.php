<?php

use core\helpers\company\PaymentHelper;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Pay account');
$this->params['breadcrumbs'][] = $this->title;

$form = ActiveForm::begin([
    'fieldConfig' => [
        'options' => ['tag' => 'li', 'class' => 'control-group'],
        'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
        'wrapperOptions' => ['class' => 'controls'],
    ],
    'options' => ['class' => 'simple_form']
]);

echo "<ol>";
echo $form->field($model, 'value');
echo $form->field($model, 'currency')->dropDownList(PaymentHelper::getCurrencyList());
echo $form->field($model, 'description');
echo $form->field($model, 'message');
echo "</ol>";

echo "<button type='submit' class='btn btn-primary'>" . Yii::t('app', 'Save') . "</button>";
$form->end();
