<?php

use core\helpers\customer\CustomerHelper;
use core\helpers\GenderHelper;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerSource;
use kartik\editable\Editable;
use yii\helpers\Html;
use yii\web\JsExpression;

$commonSettings = [
    'asPopover' => false,
    'formOptions' => ['action' => '\customer\customer\edit'],
    'ajaxSettings' => ['url' => '\customer\customer\edit'],
    'beforeInput' => Html::hiddenInput("company_customer_id")
];
?>
<div class="customer-form simple_form">
    <h2 style="margin-top: 0;">
        <p class="dark"><span class="customer-name"></span></p>
    </h2>

    <ol>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Gender') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "gender",
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'data' => GenderHelper::getGenders(),
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Birth Date') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "birth_date",
                        'inputType' => Editable::INPUT_DATE,
                        'options' => [
                            'pluginOptions' => [
                                'format' => 'yyyy-mm-dd'
                            ]
                        ]
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'City') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "city",
                        'inputType' => Editable::INPUT_TEXT
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Address') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "address",
                        'inputType' => Editable::INPUT_TEXT
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Employer') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "employer",
                        'inputType' => Editable::INPUT_TEXT
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Job') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "job",
                        'inputType' => Editable::INPUT_TEXT
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'ID Card number') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "id_card_number",
                        'inputType' => Editable::INPUT_TEXT
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'IIN') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "iin",
                        'inputType' => Editable::INPUT_TEXT
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Money spent') ?></label>
            <div class="controls">
                <b><span id="customer-revenue"></span></b>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Deposit') ?></label>
            <div class="controls">
                <b><span id="customer-deposit"></span></b>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Debt') ?></label>
            <div class="controls">
                <b><span id="customer-debt"></span></b>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Last Visit Date') ?></label>
            <div class="controls">
                <b><span id="customer-lastVisit"></span></b>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Completed Orders') ?></label>
            <div class="controls">
                <b><span id="customer-finishedOrders"></span></b>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Canceled Orders') ?></label>
            <div class="controls">
                <b><span id="customer-canceledOrders"></span></b>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Categories') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "categories",
                        'inputType' => Editable::INPUT_WIDGET,
                        'widgetClass' => 'core\helpers\color\ColorSelect2',
                        'options' => [
                            'data' => CustomerCategory::getCategoryMapSelect2(),
                            'options' => ['multiple' => true, 'placeholder' => Yii::t('app', 'Select categories')],
                            'pluginOptions' => [
                                'allowClear' => false,
                                'templateSelection' => new JsExpression('formatRepoSelection'),
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'width' => '180px',
                            ],
                            'size' => 'sm',
                            'showToggleAll' => false,
                            'maintainOrder' => true,
                        ]
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Email') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "email",
                        'inputType' => Editable::INPUT_TEXT
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'Customer Source') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "source_id",
                        'inputType' => Editable::INPUT_DROPDOWN_LIST ,
                        'data' => CustomerSource::map(),
                        'displayValueConfig' => CustomerSource::map(),
                        'options' => ['prompt' => Yii::t('app', 'Unknown')]
                    ])
                ); ?>
            </div>
        </li>
        <legend>SMS сообщения</legend>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'SMS birthday') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "sms_birthday",
                        'inputType' => Editable::INPUT_CHECKBOX,
                        'options' => ['class' => ''],
                        'displayValueConfig' => [0 => 'Нет', 1 => 'Да'],
                        'beforeInput' => Html::hiddenInput("company_customer_id") . Html::hiddenInput("sms_birthday", 0)
                    ])
                ); ?>
            </div>
        </li>
        <li class="control-group">
            <label class="control-label"><?= Yii::t('app', 'SMS exclude') ?></label>
            <div class="controls">
                <?= Editable::widget(array_merge($commonSettings, [
                        "name" => "sms_exclude",
                        'inputType' => Editable::INPUT_CHECKBOX,
                        'options' => ['style' => 'height: auto;'],
                        'displayValueConfig' => [0 => 'Нет', 1 => 'Да'],
                        'beforeInput' => Html::hiddenInput("company_customer_id") . Html::hiddenInput("sms_exclude", 0)
                     ])
                ); ?>
            </div>
        </li>
    </ol>
</div>

