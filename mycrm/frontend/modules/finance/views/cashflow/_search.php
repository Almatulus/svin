<?php

use core\helpers\HtmlHelper as Html;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyContractor;
use core\models\finance\CompanyCostItem;
use core\models\Staff;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\date\DatePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\finance\search\CashflowSearch */
?>

<?php $form = ActiveForm::begin([
    'method' => 'GET',
    'action' => ['index'],
    'fieldConfig' => [
        'template' => "{label}{input}\n{hint}\n{error}"
    ],
    'options' => ['class' => 'details-row']
]) ?>

<div class="row">
    <div class="col-md-2">
        <?= $form->field($searchModel, 'order_from')->widget(DatePicker::class, [
            'type'          => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => 'yyyy-mm-dd'
            ],
            'options' => [
                'autocomplete' => 'off'
            ]
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($searchModel, 'order_to')->widget(DatePicker::class, [
            'type'          => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => 'yyyy-mm-dd'
            ],
            'options' => [
                'autocomplete' => 'off'
            ]
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($searchModel, 'created_from')->widget(DatePicker::class, [
            'type'          => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => 'yyyy-mm-dd'
            ],
            'options' => [
                'autocomplete' => 'off'
            ]
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($searchModel, 'created_to')->widget(DatePicker::class, [
            'type'          => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => 'yyyy-mm-dd'
            ],
            'options' => [
                'autocomplete' => 'off'
            ]
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($searchModel, 'sContractor')->widget(Select2::className(), [
            'data' => CompanyContractor::map(),
            'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Contractors')],
            'pluginOptions' => [
                'allowClear' => true,
                'language' => 'ru',
            ],
            'size' => 'sm',
            'showToggleAll' => false,
        ]) ?>
    </div>
    <div class="col-sm-2">
        <?php $companyCustomer = $searchModel->sCustomer ? CompanyCustomer::findOne($searchModel->sCustomer) : null; ?>
        <?= $form->field($searchModel, 'sCustomer')->widget(Select2::className(), [
            'initValueText' => isset($companyCustomer) ? $companyCustomer->customer->fullName : "",
            'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'All Customer')],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 1,
                'language' => 'ru',
                'ajax' => [
                    'url' => ['/customer/customer/user-list'],
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(user) { return user.text; }'),
                'templateSelection' => new JsExpression('function (user) { return user.text; }'),
            ],
            'size'          => 'sm',
        ]); ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-2">
        <?= $form->field($searchModel, 'sOrder')->textInput([
            'placeholder' => Yii::t('app', 'Order Number'),
        ]) ?>
    </div>
    <div class="col-sm-2">
        <?= $form->field($searchModel, 'sCost')->widget(Select2::className(), [
            'data' => CompanyCostItem::mapFilter(),
            'options' => ['multiple' => true, 'placeholder' => Yii::t('app', 'All Cost Items')],
            'pluginOptions' => [
                'allowClear' => true,
                'language' => 'ru',
            ],
            'size' => 'sm',
            'showToggleAll' => false,
        ]) ?>
    </div>
    <div class="col-sm-2">
        <?= $form->field($searchModel, 'sCash')->widget(Select2::className(), [
            'data' => CompanyCash::map(),
            'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Cashes')],
            'pluginOptions' => [
                'allowClear' => true,
                'language' => 'ru',
            ],
            'size' => 'sm',
            'showToggleAll' => false,
        ]) ?>
    </div>
    <div class="col-sm-2">
        <?= $form->field($searchModel, 'sStaff')->widget(Select2::className(), [
            'data' => Staff::map(),
            'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Staff')],
            'pluginOptions' => [
                'allowClear' => true,
                'language' => 'ru',
            ],
            'size' => 'sm',
            'showToggleAll' => false,
        ]) ?>
    </div>
    <div class="col-sm-2">
        <?= $form->field($searchModel, 'sDivision')->widget(Select2::className(), [
            'data' => Division::getOwnCompanyDivisionsList(),
            'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Divisions')],
            'pluginOptions' => [
                'allowClear' => true,
                'language' => 'ru',
            ],
            'size' => 'sm',
            'showToggleAll' => false,
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($searchModel, 'sProductId')->widget(Select2::classname(), [
            'initValueText' => $searchModel->product ? $searchModel->product->name : '',
            'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Products')],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 1,
                'language' => 'ru',
                'ajax' => [
                    'url' => ['/warehouse/product/search'],
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {search:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(material) {return material.text; }'),
                'templateSelection' => new JsExpression('function (material) {return material.text; }'),
            ],
            'size'          => 'sm',
        ])
        ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        <button type="submit" class="btn btn-primary"><?= Yii::t('app', 'Search'); ?></button>
    </div>
    <div class="col-sm-8">
        <?= Html::a(Yii::t('app', 'Create Cashflow Income'), ['cashflow/create-income'], ['class' => 'btn right_space pull-right']) ?>
        <?= Html::a(Yii::t('app', 'Create Cashflow Expense'), ['cashflow/create-expense'], ['class' => 'btn right_space pull-right']) ?>
    </div>
</div>
<?php $form->end(); ?>
