<?php

use core\helpers\color\ColorSelect2;
use core\helpers\GenderHelper;
use core\models\company\Insurance;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerSource;
use kartik\datecontrol\DateControl;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $model \core\forms\customer\CompanyCustomerUpdateForm */
/* @var $form yii\widgets\ActiveForm */

$this->params['mainContentClass'] = 'customers';

$dateOptions = [
    'displayFormat' => 'dd/MM/yyyy',
    'autoWidget'    => false,
    'widgetClass'   => 'yii\widgets\MaskedInput',
    'widgetOptions' => [
        'definitions' => [
            'd' => [
                'validator'    => '(0[1-9]|[12]\d|3[01])',
                'cardinality'  => 2,
                'prevalidator' => [
                    ['validator' => "[0-3]", 'cardinality' => 1],
                ]
            ],
            'm' => [
                'validator'    => '(0[1-9]|1[012])',
                'cardinality'  => 2,
                'prevalidator' => [
                    ['validator' => "[0-1]", 'cardinality' => 1],
                ]
            ],
            'y' => [
                'validator'    => '(19|20)\\d{2}',
                'cardinality'  => 4,
                'prevalidator' => [
                    ['validator' => "[12]", 'cardinality' => 1],
                    ['validator' => "(19|20)", 'cardinality' => 2],
                    ['validator' => "(19|20)\\d", 'cardinality' => 3]
                ]
            ],
        ],
        'mask'        => 'd/m/y',
        'options'     => ['placeholder' => "ДД/ММ/ГГГГ"],
    ]
];
?>

<div class="customer-form">

    <?php $form = ActiveForm::begin([
        'options'     => ['class' => 'simple_form', 'enctype' => 'multipart/form-data'],
        'fieldConfig' => [
            "checkboxTemplate"        => "{beginLabel}\n{labelTitle}\n{endLabel}{beginWrapper}{input}",
            'inlineRadioListTemplate' => "{label}{beginWrapper}{input}\n{error}\n{hint}{endWrapper}",
            'options'                 => [
                'tag'   => 'li',
                'class' => 'control-group',
            ],
            'template'                => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
            'wrapperOptions'          => ['class' => 'controls'],
        ],
    ]); ?>

    <ol>
        <fieldset id="basic_data">
            <legend>Основная информация</legend>
            <?= $form->field($model, 'name')->textInput(['class' => 'string options']) ?>
            <?= $form->field($model, 'phone')
                ->widget(\yii\widgets\MaskedInput::className(), [
                    'mask' => '+7 999 999 99 99',
                ]) ?>
            <?= $form->field($model, 'lastname')->textInput(['class' => 'string options']) ?>
            <?= $form->field($model, 'patronymic')->textInput(['class' => 'string options']) ?>
            <?= $form->field($model, 'email')->textInput(['class' => 'string options']) ?>
            <?= $form->field($model, 'gender')->dropDownList(GenderHelper::getGenders()) ?>
            <?= $form->field($model, 'discount') ?>
            <?= $form->field($model, 'cashback_percent') ?>
        </fieldset>
        <fieldset id="extended_data">
            <legend>Дополнительная информация</legend>
            <?= $form->field($model, 'birth_date')->widget(DateControl::classname(), $dateOptions); ?>
            <?= $form->field($model, 'medical_record_id')->textInput() ?>
            <?= $form->field($model, 'iin')->textInput() ?>
            <?= $form->field($model, 'id_card_number')->textInput() ?>
            <?= $form->field($model, 'city')->textInput(['class' => 'string options']) ?>
            <?= $form->field($model, 'address')->textInput(['class' => 'string options']) ?>
            <?= $form->field($model, 'employer')->textInput() ?>
            <?= $form->field($model, 'job')->textInput() ?>
            <?= $form->field($model, 'phones')->widget(\unclead\multipleinput\MultipleInput::class, [
                'max'            => 5,
                'allowEmptyList' => true,
                'columns'        => [
                    [
                        'name'        => 'value',
                        'title'       => Yii::t('app', 'Phone'),
                        'type'        => \yii\widgets\MaskedInput::class,
                        'enableError' => true,
                        'options'     => [
                            'mask' => '+7 999 999 99 99'
                        ]
                    ],
                    [
                        'name' => 'key',
                        'type' => \unclead\multipleinput\MultipleInputColumn::TYPE_HIDDEN_INPUT
                    ]
                ]
            ]); ?>
            <?= $form->field($model, 'categories')->widget(ColorSelect2::className(), [
                'data'          => CustomerCategory::getCategoryMapSelect2(),
                'options'       => ['multiple' => true, 'placeholder' => Yii::t('app', 'Select categories')],
                'pluginOptions' => [
                    'allowClear'        => false,
                    'templateSelection' => new JsExpression('formatRepoSelection'),
                    'escapeMarkup'      => new JsExpression('function (markup) { return markup; }'),
                    'width'             => '240px'
                ],
                'showToggleAll' => false,
                'maintainOrder' => true,
            ]); ?>
            <?= $form->field($model, 'source_id')->dropDownList(CustomerSource::map(), [
                'prompt' => Yii::t('app', 'Unknown')
            ]) ?>
            <?= $form->field($model, 'insurance_company_id')
                ->dropDownList(
                    Insurance::map('insurance_company_id', 'insuranceCompany.name'), [
                        'prompt' => Yii::t('app', 'Unknown'),
                    ]
                ) ?>
            <?= $form->field($model, 'comments')->label('Описание')->textArea([
                'size'      => '50',
                'class'     => 'string options',
                'maxlength' => true
            ]) ?>
            <?= $form->field($model, 'insurer') ?>
            <?= $form->field($model, 'insurance_policy_number') ?>
            <?= $form->field($model, 'insurance_expire_date')->widget(DateControl::classname(), $dateOptions) ?>

            <legend>SMS сообщения</legend>
            <?= $form->field($model, 'sms_birthday')->checkbox() ?>
            <?= $form->field($model, 'sms_exclude')->checkbox() ?>
            <?= $form->field($model, 'imageFile')->fileInput()->label("Фотография") ?>
        </fieldset>
    </ol>

    <div class="form-actions fixed">
        <div class="with-max-width">
            <div class="pull_right cancel-link">
                <?= Html::a('Отмена', Yii::$app->request->referrer) ?>
            </div>
            <button class="btn btn-primary" type="submit">
                <span class="icon sprite-add_customer_save"></span>Сохранить
            </button>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>