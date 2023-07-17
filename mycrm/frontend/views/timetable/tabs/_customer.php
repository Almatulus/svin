<?php

use core\forms\customer\CompanyCustomerCreateForm;
use core\helpers\color\ColorSelect2;
use core\helpers\GenderHelper;
use core\models\customer\CustomerCategory;
use kartik\datecontrol\DateControl;
use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;

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
    ],
];
?>
<div class="customer-form">
    <a class="btn btn-sm btn-link js-show-customer-form pull-right">Изменить данные клиента</a>

    <?php
    $form = ActiveForm::begin([
        'id' => 'customer-form',
        "action" => "/customer/customer/edit",
        'fieldConfig' => [
            'checkboxTemplate' => "{beginLabel}\n{labelTitle}\n{endLabel}\n{beginWrapper}<b class='info-block'></b>\n<div class='field-block' hidden>{input}</div>{endWrapper}",
            'options' => ['tag' => 'li', 'class' => 'control-group'],
            'template' => "{label}\n{beginWrapper}<b class='info-block'></b>\n<div class='field-block' hidden>{input}\n{error}</div>{endWrapper}",
            'wrapperOptions' => ['class' => 'controls'],
        ],
        'options' => ['class' => 'simple_form', 'enctype' => 'multipart/form-data']
    ]);
    $model = new CompanyCustomerCreateForm();
    ?>

    <div class="pull-right avatar">
        <img src="/image/def_client_img.jpg" width="172" class="info-block img-rounded">
        <?= $form->field($model, 'imageFile', [
            'options' => ['tag' => null],
            'template' => '<div class="field-block" hidden>{input}</div>'
        ])->widget(FileInput::classname(), [
            'pluginOptions' => [
                'initialPreview' => [
                    '<img src="/image/def_client_img.jpg" width="172">'
                ],
                'showCaption' => false,
                'showUpload' => false,
                'browseLabel' => 'Выберите фото',
                'mainClass' => 'input-group-sm'
            ]
        ]);
        ?>
    </div>

    <input type="hidden" name="company_customer_id">
    <ol>
        <?= $form->field($model, 'gender')->dropDownList(GenderHelper::getGenders()) ?>
        <?= $form->field($model, 'birth_date')->widget(DateControl::classname(), $dateOptions); ?>
        <?= $form->field($model, 'city') ?>
        <?= $form->field($model, 'address') ?>
        <?= $form->field($model, 'employer') ?>
        <?= $form->field($model, 'job') ?>
        <?= $form->field($model, 'id_card_number') ?>
        <?= $form->field($model, 'iin') ?>
        <?= $form->field($model, 'medical_record_id') ?>
        <?= $form->field($model, 'discount') ?>
        <?= $form->field($model, 'cashback_percent') ?>
        <li class="control-group field-revenue">
            <label class="control-label"><?= Yii::t('app', 'Money spent') ?></label>
            <div class="controls"><b class='info-block'></b></div>
        </li>
        <li class="control-group field-deposit">
            <label class="control-label"><?= Yii::t('app', 'Deposit') ?></label>
            <div class="controls"><b class='info-block'></b></div>
        </li>
        <li class="control-group field-debt">
            <label class="control-label"><?= Yii::t('app', 'Debt') ?></label>
            <div class="controls"><b class='info-block'></b></div>
            <?= Html::a(
                Yii::t('app', 'Pay the debt'),
                ['/customer/customer/pay-debt'],
                [
                    'class'                 => 'btn btn-default js-pay-debt-button',
                    'data-company-customer' => 0,
                ]
            ) ?>
        </li>
        <li class="control-group field-cashback_balance">
            <label class="control-label"><?= Yii::t('app', 'Cashback') ?></label>
            <div class="controls"><b class='info-block'></b></div>
        </li>
        <li class="control-group field-lastVisit">
            <label class="control-label"><?= Yii::t('app', 'Last Visit Date') ?></label>
            <div class="controls"><b class='info-block'></b></div>
        </li>
        <li class="control-group field-finishedOrders">
            <label class="control-label"><?= Yii::t('app', 'Completed Orders') ?></label>
            <div class="controls"><b class='info-block'></b></div>
        </li>
        <li class="control-group field-canceledOrders">
            <label class="control-label"><?= Yii::t('app', 'Canceled Orders') ?></label>
            <div class="controls"><b class='info-block'></b></div>
        </li>
        <?php
        $link = \yii\helpers\Html::a(Yii::t('app', 'Add category'), '/customer/customer-category/new', [
            'class' => 'btn left_space new_customer_category_link',
            'data-model' => 'customer-category',
            'data-title' => Yii::t('app', 'Customer Category')
        ]);
        echo $form->field($model, 'categories', [
            'template' => "{label}\n{beginWrapper}<b class='info-block'></b>\n<div class='field-block' hidden>{input}$link\n{error}</div>{endWrapper}",
        ])->widget(ColorSelect2::className(), [
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
        ]); ?>
        <?= $form->field($model, 'email') ?>
        <?= $form->field($model, 'comments')->textArea() ?>
        <?= $form->field($model, 'insurer') ?>
        <?= $form->field($model, 'insurance_policy_number') ?>
        <?= $form->field($model, 'insurance_expire_date')->widget(DateControl::classname(), $dateOptions) ?>
        <legend>SMS сообщения</legend>
        <?= $form->field($model, 'sms_birthday')->checkbox(['class' => 'control-label']) ?>
        <?= $form->field($model, 'sms_exclude')->checkbox(['class' => 'control-label']) ?>
    </ol>
    <?php $form->end(); ?>
</div>

<?php
$js = <<<JS

var dialog = {};
$('.new_customer_category_link').click(function(e) {
    e.preventDefault();
    var url = $(e.target).attr('href');
    var title = $(e.target).data('title');
    $.get(url).done(function (response) {
        var dialogButtons = {
            success: {
                label: "Сохранить",
                className: "btn-primary",
                callback: function() {
                    return submitCategoryForm();
                }
            },
            danger: {
                label: "Отмена",
                className: "btn-default"
            }
        };
        dialog = dialogMessage(response, dialogButtons, title);
    }).fail(function () {
        alertMessage("Произошла ошибка");
    });
});

$('.js-new_customer_source_link').click(function(e) {
    e.preventDefault();
    bootbox.prompt("Введите наименование", function(result){
        if (result) {
            $.post("/customer/source/new", {"CustomerSource[name]" : result})
            .done(function (response) {
                response = JSON.parse(response);
                if (response.status == "success") {
                    addOption('order-customer_source_id', response.data);
                } else {
                    alertMessage("Произошла ошибка");
                }
            }).fail(function () {
                alertMessage("Произошла ошибка");
            });
        }
    });
});

function submitCategoryForm() {
    var form = $('#customer-category-form');
    $.post(form.attr('action'), form.serialize())
        .done(function (response) {
            response = JSON.parse(response);
            if (response.status == "success") {
                addColorOption('categories', response.data);
                bootbox.hideAll();
            } else {
                var message = response.error || "Произошла ошибка";
                alertMessage(message);
            }
        }).fail(function () {
            alertMessage("Произошла ошибка");
        });
    return false;
}

function addColorOption(selectId, category) {
     $('#' + selectId).append($("<option></option>")
                    .attr("value", category.id)
                    .attr('back-color', category.color)
                    .attr('font-color', category.fontColor)
                    .text(category.name));
}

JS;
$this->registerJs($js);
?>

