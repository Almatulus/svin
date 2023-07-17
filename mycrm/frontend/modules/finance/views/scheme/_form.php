<?php

use core\models\finance\Payroll;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\finance\Payroll */
/* @var $form yii\widgets\ActiveForm */

if(!$model->service_value) $model->service_value = 0;

$fieldConfig = [
    'checkboxTemplate' => "{input}\n{beginLabel}\n{labelTitle}\n{endLabel}",
    'options' => ['tag' => 'li', 'class' => 'control-group'],
    'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
    'wrapperOptions' => ['class' => 'controls'],
];

$selectOptions = [
    'class' => 'v_middle left_space',
    'style' => 'width: 75px; font-size: 12px'
];
$serviceModeSelect = Html::activeDropDownList($model, "service_mode", Payroll::getModeLabels(), $selectOptions);
?>

<div class="payroll-scheme-form">

    <?php $form = ActiveForm::begin([
        'id' => 'dynamic-form',
        'options' => ['class' => 'simple_form']
    ]); ?>

    <ol>
        <?= $form->field($model, 'name', $fieldConfig)->textInput(['class' => 'string options', 'maxlength' => true]) ?>

        <?= $form->field($model, 'service_value', array_merge($fieldConfig, [
            'template' => "{label}{beginWrapper}{input}{$serviceModeSelect}\n{hint}\n{error}{endWrapper}"
        ]))->textInput(['style' => 'width: 90px', 'class' => 'string options']) ?>

        <?= $form->field($model, 'is_count_discount', array_merge($fieldConfig, [
            'labelOptions' => ['style' => 'width: auto']
        ]))->checkbox([
            'class' => 'boolean optional control-label'
        ])?>
    </ol>

    <div class="col-sm-12">
        <h2>Уточнить значение для категорий или отдельных услуг</h2>
    </div>

    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <?php
                DynamicFormWidget::begin([
                    'widgetContainer' => 'dynamicform_wrapper_services', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                    'widgetBody' => '.container-services', // required: css class selector
                    'widgetItem' => '.dynamic-service', // required: css class
                    'min' => 0, // 0 or 1 (default 1)
                    'insertButton' => '.add-service', // css class
                    'deleteButton' => '.remove-service', // css class
                    'model' => $services[0],
                    'formId' => 'dynamic-form',
                    'formFields' => [
                        'division_service_id',
                        'service_value',
                        'service_mode',
                    ],
                ]); ?>
                <table class="container-services table" style="margin-bottom: 0">
                    <?php foreach ($services as $index => $service): ?>
                        <?= $this->render("_service", [
                            'form' => $form,
                            'service' => $service,
                            'index' => $index
                        ]) ?>
                    <?php endforeach; ?>
                </table>

                <button class="add-service btn btn-primary" style="width: 100%;">
                    Добавить услугу-исключение
                </button>
                <?php DynamicFormWidget::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <h2>Сотрудники</h2>
    </div>
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <?php
                DynamicFormWidget::begin([
                    'widgetContainer' => 'dynamicform_wrapper_staffs',
                    'widgetBody' => '.container-staffs',
                    'widgetItem' => '.dynamic-staff',
                    'min' => 0,
                    'insertButton' => '.add-staff',
                    'deleteButton' => '.remove-staff',
                    'model' => $staffs[0],
                    'formId' => 'dynamic-form',
                    'formFields' => [
                        'staff_id',
                        'started_time',
                    ],
                ]); ?>
                    <div class="container-staffs">
                        <?php foreach ($staffs as $index => $staff): ?>
                            <?= $this->render("_staff", [
                                'form' => $form,
                                'staff' => $staff,
                                'index' => $index
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                    <button class="add-staff btn btn-primary" style="width: 100%;">
                        Добавить сотрудника
                    </button>
                <?php DynamicFormWidget::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="box-buttons">
            <button class="btn btn-primary" name="button" type="submit">Сохранить</button>
            <div class="pull-right">
                <?= Html::a("отмена", Yii::$app->request->referrer) ?>
            </div>
        <div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
    $(function(){
        $(".dynamicform_wrapper_staffs").on("afterInsert", function(e, item) {
            $('.krajee-datepicker').kvDatepicker({
                language: 'ru',
                autoclose: true,
                format: 'yyyy-mm-dd'
            });
        });
    });
JS;
$this->registerJs($js);
?>