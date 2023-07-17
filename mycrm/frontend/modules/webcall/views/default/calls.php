<?php

use core\models\customer\CompanyCustomer;
use core\models\webcall\WebcallAccount;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model \core\forms\webcall\WebCallForm */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('app', 'Statistics');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Web Calls'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="webcalls-form">
    <?php
    $form = ActiveForm::begin([
        'id'     => 'webcalls-form',
        'method' => 'get',
        'action' => ['calls'],
    ]); ?>
    <?= $form->errorSummary($model); ?>
        <div class="row">
            <div class="col-sm-3">
                <?php
                echo $form->field($model, 'from_date')->widget(DatePicker::classname(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['placeholder' => Yii::t('app', 'Select date')],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]);
                ?>
            </div>
            <div class="col-sm-3">
                <?php
                echo $form->field($model, 'to_date')->widget(DatePicker::classname(), [
                    'type'          => DatePicker::TYPE_INPUT,
                    'options'       => ['placeholder' => Yii::t('app', 'Select date')],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format'    => 'yyyy-mm-dd',
                    ]
                ]);
                ?>
            </div>
            <div class="col-sm-3">
                <?php
                echo $form->field($model, 'type')->dropDownList($model->getTypes());
                ?>
            </div>
            <div class="col-sm-3">
                <?php
                $items = \yii\helpers\ArrayHelper::map(WebcallAccount::find()->company()->asArray()->all(), 'id',
                    'name');
                echo $form->field($model, 'account_id')->dropDownList($items, [
                    'prompt' => \Yii::t('app', 'All')
                ]);
                ?>
            </div>
            <div class="col-sm-3">
                <?php $companyCustomer = $model->customer_id ? CompanyCustomer::findOne($model->customer_id) : null; ?>
                <?= $form->field($model, 'customer_id')->widget(Select2::className(), [
                    'initValueText' => isset($companyCustomer) ? $companyCustomer->customer->fullName : "",
                    'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'All Customer')],
                    'pluginOptions' => [
                        'allowClear'         => true,
                        'minimumInputLength' => 2,
                        'language'           => 'ru',
                        'ajax'               => [
                            'url'      => ['/customer/customer/user-list?phone=1'],
                            'dataType' => 'json',
                            'data'     => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                        'templateResult'     => new JsExpression('function(user) { return user.text; }'),
                        'templateSelection'  => new JsExpression('function (user) { return user.text; }'),
                    ],
                    'size'          => 'sm',
                ]); ?>
            </div>
        </div>
    <div class="row">
        <div class="col-sm-12">
            <?php
            if (empty($model->getResult()->results)) {
                echo Html::tag('h3', Yii::t('app', 'No result was found'));
            } else {
                echo $this->render('_table', [
                    'results'    => $model->getResult()->results,
                    'totalCount' => $model->getTotalCount()
                ]);
            }
            ?>
        </div>
    </div>
    <div class="form-actions">
        <div class="with-max-width">
            <?= Html::submitButton(Yii::t('app', 'Refresh'), [
                'class' => 'btn btn-primary'
            ]) ?>
            <?= Html::a(Yii::t('app', 'Settings'), ['settings'], [
                'class' => 'btn btn-default'
            ]) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
