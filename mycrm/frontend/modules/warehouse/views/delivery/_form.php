<?php

use core\models\division\Division;
use core\models\finance\CompanyContractor;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Delivery */
/* @var $products \core\models\warehouse\DeliveryProduct[] */
/* @var $form yii\widgets\ActiveForm */

$backUrl = Yii::$app->request->referrer;
if (!$backUrl) {
    $backUrl = ['index'];
}
$contractorTemplate = "{label}\n{input}\n" . Html::a(Yii::t('app', 'new contractor'), '/finance/contractor/new', [
    'class' => 'btn left_space stock_new_entity_link',
    'id' => 'new_contractor_link',
    'data-model' => 'companycontractor',
    'data-title' => Yii::t('app', 'Contractor')
]);
?>

<div class="delivery-form">

    <?php $form = ActiveForm::begin([
        'id' => 'delivery-form',
        'fieldConfig' => ['options' => ['class' => '']],
        'options' => ['class' => 'simple_form new_stock_entity']
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $this->render('_dynamic_form', ['form' => $form, 'model' => $model, 'products' => $products]) ?>

    <div class="simple_row">
        <fieldset>
            <ol>
                <li class="control-group">
                    <div class="controls">
                        <?= $form->field($model, 'contractor_id', [
                            'template' => $contractorTemplate,
                        ])->widget(Select2::className(), [
                                'options' => ['placeholder' => Yii::t('app', 'Enter the name or select from list')],
                                'data' => CompanyContractor::map(),
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
                        <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList()) ?>
                    </div>
                </li>
                <li class="control-group">
                    <div class="controls">
                        <?= $form->field($model, 'invoice_number')->textInput(['maxlength' => true]) ?>
                    </div>
                </li>
                <li class="control-group">
                    <div class="controls">
                        <?= $form->field($model, "delivery_date")
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
                <li class="control-group">
                    <div class="controls">
                        <?= $form->field($model, 'notes')->textarea() ?>
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
