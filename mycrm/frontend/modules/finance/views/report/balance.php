<?php

use core\helpers\HtmlHelper as Html;
use core\models\customer\CompanyCustomer;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;


/* @var $this yii\web\View */
/* @var $model core\forms\customer\StatisticForm */

$this->title = Yii::t('app', 'Balance report');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label'    => $this->title
];
?>

<div class="report-balance">
    <?php
    $form = ActiveForm::begin([
        'action'      => ['balance'],
        'method'      => 'get',
        'fieldConfig' => [
            'template' => "{input}\n{hint}\n{error}"
        ]
    ]); ?>
    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, 'from', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'From date') . '</span>{input}</div>',
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'to', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'To date') . '</span>{input}</div>',
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-3">
            <?php $companyCustomer = $model->customer_id ? CompanyCustomer::findOne($model->customer_id) : null; ?>
            <?= $form->field($model, 'customer_id')->widget(Select2::class, [
                'initValueText' => isset($companyCustomer) ? $companyCustomer->customer->fullName : "",
                'options'       => ['class' => 'right_space', 'prompt' => Yii::t('app', 'All Customer')],
                'pluginOptions' => [
                    'allowClear'         => true,
                    'minimumInputLength' => 1,
                    'language'           => 'ru',
                    'ajax'               => [
                        'url'      => ['/customer/customer/user-list'],
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
        <div class="col-sm-2">
            <?= $form->field($model, 'type', ['inline' => true])->radioList([
                Yii::t("app", "Deposit"),
                Yii::t("app", "Debt")
            ])->label(false); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="pull-left">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>

            <div class="form-group pull-right">
                <?= Html::a(Yii::t('app', 'Export'), 'balance-export?' . Yii::$app->request->queryString, ['class' => 'btn btn-default js-export-report']) ?>
            </div>
        </div>
    </div>
    <?php $form->end(); ?>

    <div class="row">
        <div class="col-md-12">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'showFooter'   => true,
                'summary'      => Html::getSummary(),
                'columns'      => [
                    [
                        'attribute' => 'customer.fullName',
                        'label'     => Yii::t('app', 'Customer')
                    ],
                    'customer.phone',
                    [
                        'label'  => Yii::t('app', 'Deposit'),
                        'value'  => function ($model) {
                            return Yii::$app->formatter->asDecimal(
                                $model->balance >= 0 ? $model->balance : 0
                            );
                        },
                        'hAlign' => 'right',
                        'footer' => Yii::$app->formatter->asDecimal((clone $dataProvider->query)->andWhere([
                            '>',
                            'balance',
                            0
                        ])->sum('balance')),
                    ],
                    [
                        'label'  => Yii::t('app', 'Debt'),
                        'value'  => function ($model) {
                            return Yii::$app->formatter->asDecimal(
                                $model->balance < 0 ? $model->balance : 0
                            );
                        },
                        'hAlign' => 'right',
                        'footer' => Yii::$app->formatter->asDecimal((clone $dataProvider->query)->andWhere([
                            '<',
                            'balance',
                            0
                        ])->sum('balance')),
                    ],
                    [
                        'class'            => 'kartik\grid\ExpandRowColumn',
                        'expandTitle'      => 'Подробнее',
                        'collapseTitle'    => 'Скрыть',
                        'width'            => '50px',
                        'value'            => function ($model, $key, $index) {
                            return GridView::ROW_COLLAPSED;
                        },
                        'detail'           => function (CompanyCustomer $model, $key, $index, $column) {
                            return Yii::$app->controller->renderPartial('partials/_expand_row', ['model' => $model]);
                        },
                        'expandOneOnly'    => true,
                        'allowBatchToggle' => false
                    ]
                ],
            ]); ?>
        </div>
    </div>
</div>
