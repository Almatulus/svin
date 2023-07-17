<?php

use core\helpers\order\OrderConstants;
use core\models\company\Referrer;
use core\models\customer\CustomerSource;
use core\models\division\Division;
use core\models\ServiceCategory;
use core\models\Staff;
use core\models\user\User;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\order\search\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'fieldConfig' => [
            'template' => "{input}",
        ]
    ]); ?>

    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'number')->textInput(
                ['class' => 'right_space form-control', 'placeholder' => $model->getAttributeLabel('number')]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'from_date')->widget(\kartik\date\DatePicker::className(), [
                'value' => (new DateTime())->modify('-1 month')->format('Y-m-d H:i:s'),
                'options' => ['class' => 'right_space', 'placeholder' => Yii::t('app', 'From')],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'to_date')->widget(\kartik\date\DatePicker::className(), [
                'value' => (new DateTime())->format('Y-m-d H:i:s'),
                'options' => ['class' => 'right_space', 'placeholder' => Yii::t('app', 'To')],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'status')->widget(Select2::className(), [
                'data'          => OrderConstants::getUniqueStatuses(),
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'Select status')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <?php if (Yii::$app->user->identity->company->show_referrer): ?>
        <div class="col-sm-3">
            <?= $form->field($model, 'referrer_id')->widget(Select2::className(), [
                'data'          => Referrer::map(),
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'Select referrer')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <?php endif; ?>
        <div class="col-sm-3">
            <?= $form->field($model, 'division_id')->widget(Select2::className(), [
                'data'          => Division::getOwnDivisionsNameList(),
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'Select company division')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'created_user_id')
                     ->widget(Select2::className(), [
                         'data'          => ArrayHelper::merge([-1 => 'Онлайн-запись'], ArrayHelper::map(
                             User::find()->enabled()->company()->all(),
                             'id',
                             'fullName'
                         )),
                         'options'       => [
                             'class'  => 'right_space',
                             'prompt' => Yii::t('app', 'Select created user')
                         ],
                         'pluginOptions' => ['allowClear' => true],
                         'size'          => Select2::SMALL,
                     ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'company_customer_id')->widget(Select2::className(), [
                'initValueText' => isset($model->companyCustomer) ? $model->companyCustomer->customer->fullName : "",
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'Select customer')],
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
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'staff_id')->widget(Select2::className(), [
                'data'          => Staff::getOwnCompanyStaffList(),
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'Select staff')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'division_service_id')->widget(Select2::className(), [
                'data'          => \core\models\division\DivisionService::getOwnCompanyDivisionServicesList(),
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'Select service')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'service_categories')->widget(Select2::className(), [
                'data'          => ArrayHelper::map(ServiceCategory::getCompanyCategories(), 'id', 'name'),
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'All Categories'), 'multiple' => true],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'source_id')->widget(Select2::className(), [
                'data'          => ArrayHelper::merge(
                    [-1 => Yii::t('yii', '(not set)')],
                    CustomerSource::map()
                ),
                'options'       => [
                    'class'  => 'right_space',
                    'prompt' => Yii::t('app', '--- Select source ---')
                ],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'is_paid')->widget(Select2::className(), [
                'data'          => [
                    0 => Yii::t('app', 'Unpaid'),
                    1 => Yii::t('app', 'Paid'),
                ],
                'options'       => [
                    'class'  => 'right_space',
                    'prompt' => Yii::t('app', 'Wage')
                ],
                'pluginOptions' => ['allowClear' => true],
                'size'          => Select2::SMALL,
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 right-buttons">
            <div class="customer-actions inline_block">
                <div class="dropdown">
                    <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
                        Действия <b class="caret"></b>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= Html::a('<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export fetched to Excel'),
                                ['export', 'mode' => 0]) ?>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-file-excel"></i> ' . Yii::t('app', 'Export all to Excel'),
                                ['export', 'mode' => 1]) ?>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-redo"></i> ' . Yii::t('app', 'Return orders'), "javascript:;",
                                [ 'class' => 'js-reset-order' ]) ?>
                        </li>
                    </ul>
                </div>
            </div>
            <?= Html::submitButton(Yii::t('app', 'Find Orders'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>


<?php ActiveForm::end(); ?>

</div>
