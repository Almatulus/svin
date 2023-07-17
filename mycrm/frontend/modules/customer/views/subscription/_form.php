<?php

use core\models\customer\CustomerSubscription;
use core\models\division\DivisionService;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerSubscription */
/* @var $form yii\widgets\ActiveForm */

$backUrl = Yii::$app->request->referrer;
if (!$backUrl) {
    $backUrl = ['index'];
}
?>

<div class="customer-subscription-form">

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'options' => ['tag' => 'li', 'class' => 'control-group'],
            'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
            'wrapperOptions' => ['class' => 'controls'],
        ],
        'options' => ['class' => 'simple_form']
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <ol>
        <?php
        if (!$model->isNewRecord) {
            echo $form->field($model, 'key')->textInput(['disabled' => true]);
        }
        ?>
        <?= $form->field($model, 'company_customer_id')->widget(Select2::classname(), [
            'initValueText' => isset($model->companyCustomer) ? $model->companyCustomer->customer->getFullName() : '',
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
        ]) ?>

        <?= $form->field($model, 'end_date')->widget(DatePicker::className(), [
            'options' => ['placeholder' => Yii::t('app', 'Select date')],
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]) ?>

        <?= $form->field($model, 'start_date')->widget(DatePicker::className(), [
            'options' => ['placeholder' => Yii::t('app', 'Select date')],
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]) ?>

        <?= $form->field($model, 'first_visit')->widget(DatePicker::className(), [
            'options' => ['placeholder' => Yii::t('app', 'Select date')],
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]) ?>

        <?= $form->field($model, 'type')->dropDownList(CustomerSubscription::getTypes()) ?>

        <li class="control-group string subscription_services">
            <label class="string control-label" for="subscription_service_ids">Услуги</label>
            <div class="controls">
                <div id="services_tree"></div>
                <input id="subscription_service_ids" name="CustomerSubscription[services_ids]" type="hidden">
            </div>
        </li>

        <?= $form->field($model, 'number_of_persons')->textInput() ?>

        <?= $form->field($model, 'quantity')->textInput()->hint(Yii::t('app', 'Number of days(visits)')) ?>

        <?= $form->field($model, 'price')->textInput() ?>
    </ol>

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

    <?php ActiveForm::end(); ?>

</div>

<?php

$selected = [];
if (!$model->isNewRecord) {
    $selected = \yii\helpers\ArrayHelper::getColumn($model->services, 'division_service_id');
}
$source = json_encode(DivisionService::getCompanyTreeStructure($selected));

$js = <<<JS
    $("#services_tree").fancytree({
        source: {$source}, // initial source
        checkbox: true,
        icon: false,
        expanded: true,
        selectMode: 3,
        strings:  {loading: "Загрузка...", loadError: "Произошла ошибка!", moreData: "Еще...", noData: "Нет данных."}
    });

    $("form").submit(function() {
        var selection = jQuery.map(
        jQuery('#services_tree').fancytree('getRootNode').tree.getSelectedNodes(),
            function( node ) {
                if ($.isNumeric(node.key)) {
                    return Number(node.key);
                }
            }
        );

        $('#subscription_service_ids').val(JSON.stringify(selection));
        return true;
    });
JS;
$this->registerJs($js);
?>