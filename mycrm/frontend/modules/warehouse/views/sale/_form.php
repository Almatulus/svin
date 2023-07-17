<?php

use core\models\Payment;
use core\models\Staff;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\warehouse\Sale;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Sale */
/* @var $form yii\widgets\ActiveForm */

$backUrl = Yii::$app->request->referrer;
if (!$backUrl) {
    $backUrl = ['index'];
}
$customerFieldTemplate = "{label}\n{input}\n" . Html::a(Yii::t('app', 'new customer'), '/customer/customer/new', [
    'class' => 'btn left_space stock_new_entity_link',
    'id' => 'new_customer_link',
    'data-model' => 'customer',
    'data-title' => Yii::t('app', 'Customer')
]);
?>

<div class="sale-form">

<?php $form = ActiveForm::begin([
    'id' => 'sale-form',
    'fieldConfig' => ['options' => ['class' => '']],
    'options' => ['class' => 'simple_form  new_stock_entity']
]); ?>

<?= $form->errorSummary($model); ?>

<?= $this->render('_dynamic_form', ['form' => $form, 'model' => $model, 'products' => $saleProducts]) ?>

<div class="simple_row">
    <fieldset>
        <ol>
            <li class="control-group">
                <div class="controls">
                    <?= $form->field($model, 'company_customer_id', [
                        'template' => $customerFieldTemplate,
                    ])->widget(Select2::classname(), [
                        'initValueText' => isset($model->sale->companyCustomer) ?
                            $model->sale->companyCustomer->customer->fullName . $model->sale->companyCustomer->customer->phone : '',
                        'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'Enter the name or phone number')],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 3,
                            'ajax' => [
                                'url' => ['/customer/customer/user-list'],
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(user) { return user.text; }'),
                            'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                            'width' => '240px'
                        ],
                        'size' => 'sm'
                    ]);
                    ?>
                </div>
            </li>
            <li class="control-group">
                <label class="control-label"><?= Yii::t('app', 'Subtotal') ?></label>
                <div class="controls">
                    <span class="sale-subtotal"><?= Sale::getSaleData($saleProducts)['subtotal'] ?></span>
                    <span class="light-font">(<?= Yii::t('app', 'VAT') ?>: <span class="sale-tax"><?= Sale::getSaleData($saleProducts)['tax'] ?></span>)</span>
                </div>
            </li>
            <li class="control-group">
                <label class="control-label"><?= Yii::t('app', 'Total sum') ?></label>
                <div class="controls">
                    <div class="h2 no-margin products-total"><?= Sale::getSaleData($saleProducts)['total'] ?></div>
                </div>
            </li>
            <li class="control-group">
                <label class="control-label"><?= Yii::t('app', 'Paid') ?></label>
                <div class="controls">
                    <div class="inline_block">
                        <?= $form->field($model, 'paid')->textInput(['style' => 'width: 70px'])->label(false) ?>
                    </div>
                    <div class="inline_block">
                        <?= $form->field($model, 'cash_id')->dropDownList(CompanyCash::map(),
                            ['class' => 'left_space right_space auto_width', 'style' => 'vertical-align: middle'])->label(false); ?>
                    </div>
                </div>
            </li>
            <li class="control-group">
                <div class="controls">
                    <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList(), [
                        'prompt' => Yii::t('app', 'Select company division')
                    ]) ?>
                </div>
            </li>
            <li class="control-group">
                <div class="controls">
                    <?= $form->field($model, 'staff_id')->widget(Select2::className(), [
                            'options' => ['placeholder' => Yii::t('app', 'Enter the name or select from list')],
                            'data' => Staff::getOwnCompanyStaffList(),
                            'size' => 'sm',
                            'pluginOptions' => [
                                'width' => '240px',
                                'allowClear' => true
                            ]
                        ])
                    ?>
                </div>
            </li>
            <li class="control-group">
                <div class="controls">
                    <?= $form->field($model, 'payment_id')->dropDownList(Payment::getPaymentsList(), [
                        'prompt' => Yii::t('app', 'Select payment')
                    ]) ?>
                </div>
            </li>
            <li class="control-group">
                <div class="controls">
                    <?= $form->field($model, "sale_date")
                        ->widget(DatePicker::className(), [
                            'options' => ['placeholder' => Yii::t('app', 'Select date')],
                            'type' => DatePicker::TYPE_INPUT,
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                            ]
                    ]); ?>
                </div>
            </li>
        </ol>
    </fieldset>
</div>

<div class="form-actions">
    <div class="with-max-width">
        <div class="pull_right cancel-link">
            <?= Html::a(Yii::t('app', 'Cancel'), $backUrl) ?>
        </div>
        <button class="btn btn-primary" type="submit">
            <span class="icon sprite-add_customer_save"></span><?= Yii::t('app', 'Save') ?>
        </button>
    </div>
</div>
<?php $form->end(); ?>

</div>