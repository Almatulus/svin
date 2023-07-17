<?php

/* @var $this yii\web\View */

use core\helpers\division\DivisionHelper;
use core\models\division\Division;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\MaskedInput;

/* @var $model \core\forms\company\CompanyUpdateForm */
/* @var $form yii\widgets\ActiveForm */

$this->title                   = Yii::t('app', 'Company');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $model->company->name
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
                    'class' => 'control-group'
                ],
                'template'                => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
                'wrapperOptions'          => ['class' => 'controls'],
            ],
            'options'     => ['class' => 'simple_form']
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
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'head_surname')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'head_patronymic')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'address')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'iik')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'bank')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'bin')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'bik')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'phone')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'license_number')
                              ->textInput([
                                  'class'     => 'string options',
                                  'maxlength' => true
                              ]);
                    echo $form->field($model, 'license_issued')
                              ->widget(DatePicker::classname(), [
                                  'type'          => DatePicker::TYPE_INPUT,
                                  'options'       => [
                                      'placeholder' => Yii::t('app',
                                          'Select date')
                                  ],
                                  'pluginOptions' => [
                                      'autoclose' => true,
                                      'format'    => 'yyyy-mm-dd',
                                  ]
                              ]);
                    echo $form->field($model, 'widget_prefix');
                    echo $form->field($model, 'online_start')
                              ->widget(MaskedInput::className(), [
                                  'mask' => '99:99',
                              ]);
                    echo $form->field($model, 'online_finish')
                              ->widget(MaskedInput::className(), [
                                  'mask' => '99:99',
                              ]);
                    echo $form->field($model, 'notify_about_order')->checkbox();

                    echo $form->field($model, 'cashback_percent');
                    ?>
                    <li class="control-group file optional">
                        <label class="file optional control-label" for="staff_avatar">Логотип (макс. 1Мб)</label>
                        <div class="controls">
                            <div class="btn fileinput-button js-image-field-wrapper">
                                <span class="icon sprite-add_photo_blue"></span>
                                <span>
                                    <?= $model->company->logo_id === null ?
                                        Yii::t('app', 'Add') :
                                        Yii::t('app', 'Update') ;?>
                                </span>
                                <?= $form->field($model, 'image_file', [
                                    'options' => [
                                        'tag' => null,
                                    ]
                                ])->fileInput([
                                    'class'    => 'js-image-field',
                                    'template' => '{input}'
                                ])->label(false) ?>
                            </div>
                            <span class="chosen_photo hidden">
                                Выбранное фото:<span class="photo_name"></span>&nbsp; &nbsp;
                                <a href="javascript:void(0)">Изменить</a>
                            </span>
                        </div>
                        <?php if ($model->company->logo_id !== null): ?>
                            <div class="avatar">
                                <?= Html::img(
                                    $model->company->logo->getPath(),
                                    ['height' => 150]
                                ) ?>
                            </div>
                        <?php endif; ?>
                    </li>
                </ol>
            </div>
            <div class="col-sm-6">
                <?= \yii\widgets\DetailView::widget([
                    'model'      => $model,
                    'attributes' => [
                        [
                            'label' => Yii::t('app', 'Tariff'),
                            'value' => $model->company->tariff ? $model->company->tariff->name : Yii::t('yii',
                                '(not set)')
                        ],
                        [
                            'label'  => Yii::t('app', 'Balance'),
                            'format' => 'html',
                            'value'  => function (
                                \core\forms\company\CompanyUpdateForm $form
                            ) {
                                return Html::a(Yii::$app->formatter->asDecimal(
                                    $form->company->getBalance()),
                                    ['payment']
                                );
                            },
                        ],
                        [
                            'label' => 'SMS',
                            'value' => $model->company->getSmsLimit()
                                       . ' SMS осталось',
                        ],
                        [
                            'label'  => Yii::t('app', 'Last Payment'),
                            'format' => 'date',
                            'value'  => $model->company->lastTariffPayment->start_date ?? null,
                        ],
                        [
                            'label'  => Yii::t('app', 'Next Payment'),
                            'format' => 'date',
                            'value'  => $model->company->lastTariffPayment->nextPaymentDate ?? null,
                        ],
                        [
                            'label'  => Yii::t('app', 'Online widget link'),
                            'value'  => Html::a(
                                $model->company->getOnlineWidgetLink(),
                                $model->company->getOnlineWidgetLink(),
                                ['target' => '_blank']),
                            'format' => 'raw',
                        ],
                        [
                            'label'  => Yii::t('app', 'Messaging'),
                            'value'  => \core\helpers\CompanyHelper::getMessagingName($model->company->messaging_type),
                            'format' => 'raw',
                        ],
                        [
                            'label'  => Yii::t('app', 'URL'),
                            'value'  => $model->company->chatapi_url,
                            'format' => 'raw',
                        ],
                        [
                            'label'  => Yii::t('app', 'Token'),
                            'value'  => $model->company->chatapi_token,
                            'format' => 'raw',
                        ],
                    ],
                    'options'    => ['class' => 'table table-striped table-bordered data_table no_hover']
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?= GridView::widget([
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
                                       . DivisionHelper::getStatusLabel($data->status)
                                       . "</span>";
                            }
                        ],
                        [
                            'attribute' => 'name',
                            'format'    => 'html',
                            'value'     => function ($data) {
                                return Html::a($data->name, [
                                    "/division/division/update",
                                    "id" => $data->id
                                ]);
                            }
                        ],
                        'company.name',
                        'city.name',
                        [
                            'class'      => 'yii\grid\ActionColumn',
                            'controller' => '/division/division',
                            'template'   => '{delete}'
                        ],
                    ],
                ]); ?>
            </div>
        </div>
        <div class="form-actions">
            <div class="with-max-width">
                <?= Html::submitButton(Yii::t('app', 'Update'), [
                    'class' => 'btn btn-primary',
                    'name'  => 'submit-button'
                ]) ?>
                <?= Html::a(Yii::t('app', 'Add new division'),
                    ['/division/division/create'], [
                        'class' => 'btn btn-default'
                    ]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
