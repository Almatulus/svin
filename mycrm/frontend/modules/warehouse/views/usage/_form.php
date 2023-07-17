<?php

use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\Staff;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Usage */
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
    'id' => 'usage-form',
    'fieldConfig' => ['options' => ['class' => '']],
    'options' => ['class' => 'simple_form new_stock_entity']
]); ?>

<?= $form->errorSummary($model); ?>

<?= $this->render('_dynamic_form', ['form' => $form, 'model' => $model, 'products' => $usageProducts]) ?>

<div class="simple_row">
    <fieldset>
        <ol>
            <li class="control-group">
                <div class="controls">
                    <?php
                    if ($model->company_customer_id) {
                        $companyCustomer = CompanyCustomer::findOne($model->company_customer_id);
                    }
                    ?>
                    <?= $form->field($model, 'company_customer_id', [
                        'template' => $customerFieldTemplate,
                    ])->widget(Select2::classname(), [
                        'initValueText' => isset($companyCustomer) ? $companyCustomer->customer->fullName : '',
                        'options'       => ['multiple' => false, 'placeholder' => Yii::t('app', 'Enter the name or phone number')],
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
                <div class="controls">
                    <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList()) ?>
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
                           ,]
                        ])
                    ?>
                </div>
            </li>
            <li class="control-group">
                <div class="controls">
                    <?= $form->field($model, 'comments')->textarea() ?>
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
        <button class="btn btn-primary" type="submit" <?= $disabled ?>>
            <span class="icon sprite-add_customer_save"></span><?= Yii::t('app', 'Save') ?>
        </button>
    </div>
</div>
<?php $form->end(); ?>

</div>
