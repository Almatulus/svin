<?php

use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Tariff Payment');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Companies'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = $company->name;

$form = ActiveForm::begin([
    'fieldConfig' => [
        'options'        => ['tag' => 'li', 'class' => 'control-group'],
        'template'       => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
        'wrapperOptions' => ['class' => 'controls'],
    ],
    'options'     => ['class' => 'simple_form']
]);

echo "<ol>";
echo $form->field($model, 'sum');
echo $form->field($model, 'start_date')->widget(DatePicker::class, [
    'type'          => DatePicker::TYPE_INPUT,
    'options'       => ['placeholder' => Yii::t('app', 'Select date')],
    'pluginOptions' => [
        'autoclose' => true,
        'format'    => 'yyyy-mm-dd',
    ]
]);
echo $form->field($model, 'period')->hint('месяцев');
echo "</ol>";

echo "<button type='submit' class='btn btn-primary'>" . Yii::t('app', 'Save') . "</button>";
$form->end();
