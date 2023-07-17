<?php

use core\helpers\CompanyHelper;
use core\helpers\division\DivisionHelper;
use core\models\division\Division;
use core\models\ServiceCategory;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;

/* @var $model \frontend\modules\admin\forms\CompanyUpdateForm */
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

$this->title                   = Yii::t('app', 'Company');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $model->company->name,
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="company-update">
    <div class="company-form">
        <?php
        $form = ActiveForm::begin([
            'id'          => 'dynamic-form',
            'fieldConfig' => [
                "checkboxTemplate"        => "{beginLabel}\n{labelTitle}\n{endLabel}{beginWrapper}{input}",
                'inlineRadioListTemplate' => "{label}{beginWrapper}{input}\n{error}\n{hint}{endWrapper}",
                'options'                 => [
                    'tag'   => 'li',
                    'class' => 'control-group',
                ],
                'template'                => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
                'wrapperOptions'          => ['class' => 'controls'],
            ],
            'options'     => ['class' => 'simple_form'],
        ]); ?>
        <?= $form->errorSummary($model); ?>
        <div class="row">
            <div class="col-sm-6 simple_form">
                <ol>
                    <?php
                    echo $form->field($model, 'name')
                        ->textInput(['class' => 'string options']);
                    echo $form->field($model, 'head_name')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'head_surname')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'head_patronymic')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'address')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'iik')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'bank')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'bin')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'bik')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'phone')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'license_number')
                        ->textInput([
                            'class'     => 'string options',
                            'maxlength' => true,
                        ]);
                    echo $form->field($model, 'license_issued')
                        ->widget(DatePicker::classname(), [
                            'type'          => DatePicker::TYPE_INPUT,
                            'options'       => [
                                'placeholder' => Yii::t('app',
                                    'Select date'),
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format'    => 'yyyy-mm-dd',
                            ],
                        ]);

                    echo $form->field($model, 'publish')
                        ->dropDownList(CompanyHelper::getPublishStatuses());
                    echo $form->field($model, 'status')
                        ->dropDownList(CompanyHelper::getStatuses());
                    echo $form->field($model, 'enable_web_call')
                        ->dropDownList(CompanyHelper::getWebCallStatus());
                    echo $form->field($model, 'tariff_id')
                        ->dropDownList(\core\models\company\Tariff::map(), [
                            'prompt' => Yii::t('app', 'Select'),
                        ]);
                    echo $form->field($model, 'file_manager_enabled')
                        ->checkbox();
                    echo $form->field($model, 'show_referrer')->checkbox();
                    echo $form->field($model, 'show_new_interface')->checkbox();
                    echo $form->field($model, 'unlimited_sms')->checkbox();
                    echo $form->field($model, 'notify_about_order')->checkbox();
                    echo $form->field($model, 'limit_auth_time_by_schedule')->checkbox();
                    echo $form->field($model, 'enable_integration')->checkbox();
                    echo $form->field($model, 'category_id')
                        ->dropDownList(
                            ArrayHelper::map(ServiceCategory::getRootCategories(),
                                'id', 'name'),
                            ['prompt' => Yii::t('app', 'Select type')]
                        );
                    echo $form->field($model, 'interval');
                    echo $form->field($model, 'cashback_percent');
                    ?>
                </ol>
            </div>
            <div class="col-sm-6">
                <?php try {
                    echo DetailView::widget([
                        'model'      => $model,
                        'attributes' => [
                            [
                                'label' => Yii::t('app', 'Tariff'),
                                'value' => $model->company->tariff
                                    ? $model->company->tariff->name
                                    : Yii::t('yii',
                                        '(not set)'),
                            ],
                            [
                                'label'  => Yii::t('app', 'Balance'),
                                'format' => 'html',
                                'value'  => function (
                                    frontend\modules\admin\forms\CompanyUpdateForm $form
                                ) {
                                    return Html::a(Yii::$app->formatter->asDecimal(
                                        $form->company->getBalance()),
                                        [
                                            'payment',
                                            'id' => $form->company->id,
                                        ]
                                    );
                                },
                            ],
                            [
                                'label' => 'SMS',
                                'value' => $model->company->getSmsLimit()
                                    .' SMS осталось',
                            ],
                            [
                                'label'  => Yii::t('app', 'Last Payment'),
                                'format' => 'html',
                                'value'  => function (
                                    frontend\modules\admin\forms\CompanyUpdateForm $form
                                ) {
                                    $lastPayment = $form->company->lastTariffPayment;
                                    if ( ! $lastPayment) {
                                        return null;
                                    }

                                    return Html::a(
                                        Yii::$app->formatter->asDate($lastPayment->start_date),
                                        [
                                            'payment-logs',
                                            'id' => $form->company->id,
                                        ]
                                    );
                                },
                            ],
                            [
                                'label'  => Yii::t('app', 'Next Payment'),
                                'format' => 'date',
                                'value'  => $model->company->lastTariffPayment->nextPaymentDate
                                    ?? null,
                            ],
                            [
                                'label'  => Yii::t('app', 'Online widget link'),
                                'value'  => Html::a(
                                    $model->company->getOnlineWidgetLink(),
                                    $model->company->getOnlineWidgetLink(),
                                    ['target' => '_blank']),
                                'format' => 'raw',
                            ],
                        ],
                        'options'    => ['class' => 'table table-striped table-bordered data_table no_hover'],
                    ]);
                } catch (Exception $e) {
                }
                ?>
                <?php
                echo Html::a(
                    Yii::t('app', 'Add to balance'),
                    ['add-payment', 'id' => $model->company->id],
                    ['class' => 'btn btn-primary']
                );
                echo Html::a(
                    'Оплатить за использование системы',
                    ['pay-tariff', 'id' => $model->company->id],
                    ['class' => 'btn btn-success pull-right']
                );
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php try {
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns'      => [
                            [
                                'attribute' => 'status',
                                'format'    => 'html',
                                'value'     => function ($data) {
                                    $class = "label";
                                    switch ($data->status) {
                                        case Division::STATUS_ENABLED:
                                            $class .= " label-success";
                                            break;
                                        case Division::STATUS_DISABLED:
                                            $class .= " label-danger";
                                            break;
                                    }

                                    return "<span class='{$class}'>"
                                        .DivisionHelper::getStatusLabel($data->status)
                                        ."</span>";
                                },
                            ],
                            [
                                'attribute' => 'name',
                                'format'    => 'html',
                                'value'     => function ($data) {
                                    return Html::a($data->name, [
                                        "/division/division/update",
                                        "id" => $data->id,
                                    ]);
                                },
                            ],
                            'company.name',
                            'city.name',
                            [
                                'class'      => 'yii\grid\ActionColumn',
                                'controller' => 'division/division',
                                'template'   => '{schedule} {delete}',
                                'buttons'    => [
                                    'schedule' => function ($url, $model) {

                                        return Html::a('<span class="glyphicon glyphicon-calendar"></span>',
                                            [
                                                '/division/division/schedule',
                                                'id' => $model->id,
                                            ],
                                            [
                                                'title' => Yii::t('app',
                                                    'schedule'),
                                            ]
                                        );
                                    },
                                ],
                            ],
                        ],
                    ]);
                } catch (Exception $e) {
                } ?>
            </div>
        </div>
        <div class="form-actions">
            <div class="with-max-width">
                <?= Html::submitButton(Yii::t('app', 'Update'), [
                    'class' => 'btn btn-primary',
                    'name'  => 'submit-button',
                ]) ?>
                <?= Html::a(Yii::t('app', 'Add new division'),
                    ['/division/division/create'], [
                        'class' => 'btn btn-default',
                    ]) ?>
                <?php if (0 === $model->company->getTeethDiagnoses()
                        ->count()
                ): ?>
                    <?= Html::a(
                        Yii::t('app', 'Generate Teeth Diagnoses'),
                        [
                            'generate-teeth-diagnoses',
                            'id' => $model->company->id,
                        ],
                        ['class' => 'btn btn-default']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
