<?php

use core\helpers\CompanyHelper;
use core\models\company\Tariff;
use core\models\ServiceCategory;
use frontend\modules\admin\forms\CompanyCreateForm;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model CompanyCreateForm */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Company'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-create">

    <div class="company-form">
        <?php
        $form = ActiveForm::begin([
            'id'          => 'dynamic-form',
            'fieldConfig' => [
                "checkboxTemplate"        => "{beginLabel}\n{labelTitle}\n{endLabel}{beginWrapper}{input}",
                'inlineRadioListTemplate' => "{label}{beginWrapper}{input}\n{error}\n{hint}{endWrapper}",
                'options'                 => [
                    'tag'   => 'li',
                    'class' => 'control-group'
                ],
                'template'                => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
                'wrapperOptions'          => ['class' => 'controls'],
            ],
            'options'     => ['class' => 'simple_form']
        ]); ?>
        <?= $form->errorSummary($model); ?>
        <div class="row">
            <div class="col-sm-6 simple_form">
                <ol>
                    <?php
                    echo $form->field($model, 'name')->textInput(['class' => 'string options']);
                    echo $form->field($model, 'head_name')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'head_surname')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'head_patronymic')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'address')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'iik')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'bank')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'bin')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'bik')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'phone')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'license_number')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'license_issued')->widget(DatePicker::classname(), [
                        'type' => DatePicker::TYPE_INPUT,
                        'options' => ['placeholder' => Yii::t('app', 'Select date')],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ]
                    ]);

                    echo $form->field($model, 'publish')->dropDownList(CompanyHelper::getPublishStatuses());
                    echo $form->field($model, 'status')->dropDownList(CompanyHelper::getStatuses());
                    echo $form->field($model, 'enable_web_call')->dropDownList(CompanyHelper::getWebCallStatus());
                    echo $form->field($model, 'tariff_id')->dropDownList(Tariff::map(), [
                        'prompt' => Yii::t('app', 'Select')
                    ]);
                    echo $form->field($model, 'file_manager_enabled')->checkbox();
                    echo $form->field($model, 'show_referrer')->checkbox();
                    echo $form->field($model, 'show_new_interface')->checkbox();
                    echo $form->field($model, 'unlimited_sms')->checkbox();
                    echo $form->field($model, 'notify_about_order')->checkbox();
                    echo $form->field($model, 'limit_auth_time_by_schedule')->checkbox();
                    echo $form->field($model, 'enable_integration')->checkbox();
                    echo $form->field($model, 'category_id')->dropDownList(
                        ArrayHelper::map(ServiceCategory::getRootCategories(), 'id', 'name'),
                        ['prompt' => Yii::t('app', 'Select type')]);
                    echo $form->field($model, 'interval');
                    echo $form->field($model, 'cashback_percent');
                    ?>
                </ol>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
        <div class="form-actions">
            <div class="with-max-width">
                <?= Html::submitButton(Yii::t('app', 'Create'), [
                    'class' => 'btn btn-primary',
                    'name' => 'submit-button'
                ]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
