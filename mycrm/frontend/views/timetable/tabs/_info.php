<?php

use core\helpers\OrderHelper;
use core\models\company\Insurance;
use core\models\company\Referrer;
use core\models\customer\CustomerSource;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model core\models\order\Order */
/* @var $form yii\widgets\ActiveForm */
/* @var $staff \core\models\Staff */
?>

<?= $form->errorSummary($model); ?>

<?= $form->field($model, 'id')->hiddenInput()->label(false)->error(false) ?>
<?= $form->field($model, 'company_customer_id')->hiddenInput()->label(false)->error(false) ?>
<?= $form->field($model, 'ignore_stock')->hiddenInput()->label(false)->error(false); ?>
<?= $form->field($model, 'ignoreNameWarning')->hiddenInput()->label(false)->error(false); ?>

<?= Html::hiddenInput('event-staff_id', null, ['id' => 'event-staff_id']); ?>

<div class="row">
    <div class="col-sm-6">
        <?= $form->field($model, 'customer_name')->textInput() ?>
    </div>
    <?php if ( ! $staff || $staff->canSeeCustomerPhones()) :?>
        <div class="col-sm-5">
            <?= $form->field($model, 'customer_phone')
                     ->widget(MaskedInput::className(), [
                         'mask' => '+7 999 999 99 99',
                     ]) ?>
        </div>
        <div class="col-md-1">
            <label>&nbsp;&nbsp;&nbsp;</label>
            <?= Html::button('+', [
                'id'    => 'js-add-contact',
                'class' => 'btn btn-default pull-right',
                'title' => Yii::t('app', 'Add contact info')
            ]); ?>
        </div>
    <?php endif;?>
</div>
<div class="row">
    <div class="col-sm-6 customer-reset-btn">
        <?= Html::a(Yii::t('app', 'Reset'), '#', ['class' => 'js-reset-customer']) ?>
    </div>
</div>
<div id="order-contacts"></div>
<br>
<div class="row">
    <?php
    $divisionList = Division::getOwnCompanyDivisionsList();
    $settings     = [];
    $hidden       = "";
    if (sizeof($divisionList) == 1) {
        $hidden             = "hidden";
        $model->division_id = key($divisionList);
    } else {
        $settings['prompt'] = Yii::t('app', 'Select company division');
    }
    ?>
    <div class="col-sm-6 <?= $hidden ?>">
        <?= $form->field($model, 'division_id')->dropDownList($divisionList); ?>
    </div>
    <div class="col-sm-6">
        <?= $form->field($model, 'staff_id')
                 ->widget(DepDrop::className(), [
                     'data'          => [],
                     'pluginOptions' => [
                         'depends'     => [
                             Html::getInputId($model, 'division_id')
                         ],
                         'params'      => ['event-staff_id'],
                         'placeholder' => Yii::t('app', 'Select staff'),
                         'url'         => Url::to(['/staff/search']),
                         'loading'     => true,
                         'loadingText' => Yii::t('app', 'Loading...'),
                     ],
                     'options'       => [
                         'required' => true,
                         'prompt'   => Yii::t('app', 'Select staff'),
                     ]
                 ]) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 controls">
        <label class="control-label"><?= Yii::t('app', 'Services') ?></label>
        <div id="tabbedTable" class="tabbed-table" style='display: none'>
            <div class="tabs">
                <a href="#" class="tabs-tab active" data-target="services">
                    <span class="icon right_space sprite-calendar_event"></span>
                    <span><?= Yii::t('app', 'Services') ?></span>
                </a>
                <a href="#" class="tabs-tab" data-target="products">
                    <span class="icon right_space sprite-calendar_supply_use"></span>
                    <span>Списание товаров</span>
                </a>
                <a href="#" class="tabs-tab" data-target="payments">
                    <span class="icon right_space"></span>
                    <span>Способы оплаты</span>
                </a>
            </div>
            <div class="table-wrapper">
                <div id="services" class="tabbed-table">
                    <?= Html::tag("table", '',
                        [
                            'id'    => 'servicesTable',
                            'class' => 'data_table table-services',
                            'style' => 'background-color: #ddd'
                        ]
                    );
                    ?>
                </div>
                <table id="products" style="display: none">
                    <tbody>
                    <tr>
                        <td class="box-cell">
                            <div>
                                <div class="data_table products-table no_hover">
                                    <table>
                                        <thead>
                                        <tr>
                                            <th>наименование</th>
                                            <th>ед. изм.</th>
                                            <th>цена продажи, тг</th>
                                            <th>количество</th>
                                            <th>на складе</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="products-controls">
                                    <button type="button" class="btn right_space js-add-product">добавить товар</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div id="payments" class="tabbed-table" data-balance=0
                     style="display: none">
                </div>
            </div>
        </div>
        <?= $form->field($model, 'division_service_id', [
            'options'  => ['class' => 'form-group'],
            'template' => "{input}",
        ])->widget(DepDrop::className(), [
            'data'           => [],
            'type'           => DepDrop::TYPE_SELECT2,
            'select2Options' => [
                'size'         => 'sm',
                'options'      => ['prompt' => Yii::t('app', 'Select service')],
                'pluginEvents' => [
                    'select2:select' => "servicesSelectEvent",
                    'select2:close'  => "servicesCloseEvent"
                ]
            ],
            'pluginOptions'  => [
                'depends'     => [Html::getInputId($model, 'staff_id'), Html::getInputId($model, 'insurance_company_id')],
                'initialize'  => true,
                'initDepends' => [Html::getInputId($model, 'staff_id'), Html::getInputId($model, 'insurance_company_id')],
                'placeholder' => Yii::t('app', 'Select service'),
                'url'         => Url::to(['/service-category/search']),
                'loading'     => true,
                'loadingText' => Yii::t('app', 'Loading...'),
            ],
            'options'        => ['prompt' => Yii::t('app', 'Select service')]
        ]) ?>
        <?= Html::button(Yii::t('app', 'Add service'), [
            'id'    => 'js-add-service',
            'class' => 'btn btn-sm btn-default pull-right',
            'style' => 'display: none'
        ]); ?>
    </div>
    <div class="col-sm-12 simple_form">
        <?php $options = [
            'errorOptions' => ['style' => 'margin: 0'],
            'inputOptions' => ['value' => 0]
        ];
        ?>
        <ol>
            <li class="control-group" hidden>
                <div class="controls">
                    <?= $form->field($model, 'price', $options)
                             ->textInput(['readonly' => true]) ?>
                </div>
            </li>
            <li class="control-group" hidden>
                <div class="controls">
                    <?= $form->field($model, 'productsPrice', $options)
                             ->textInput(['readonly' => true]) ?>
                </div>
            </li>
            <li class="control-group" hidden>
                <div class="controls">
                    <div class="field-order-total-price">
                        <?= Html::label(Yii::t('app',
                            'Order total price, currency'), "order-total-price",
                            ["class" => "control-label order-total-price"]) ?>
                        <?= Html::textInput("Order[order_total_price]", 0,
                            [
                                'class'    => 'form-control',
                                'id'       => 'order-total-price',
                                'readonly' => true
                            ]) ?>
                    </div>
                </div>
            </li>
            <li class="control-group">
                <div class="controls">
                    <div class="field-order-paid">
                        <?= Html::label(Yii::t('app', 'Paid, currency'),
                            "order-paid",
                            ["class" => "control-label"]) ?>
                        <?= Html::textInput("Order[paid]", 0,
                            [
                                'class'    => 'form-control',
                                'id'       => 'order-paid',
                                'readonly' => true
                            ]) ?>
                    </div>
                </div>
            </li>
            <?php $cashes = CompanyCash::map();
            if (sizeof($cashes) > 0) { ?>
                <li class="control-group">
                    <div class="controls">
                        <?= $form->field($model, 'company_cash_id', $options)
                                 ->dropDownList($cashes) ?>
                    </div>
                </li>
            <?php } ?>
            <li class="control-group color order_color">
                <div class="controls">
                    <?= $form->field($model, 'color')
                             ->dropDownList(OrderHelper::getCssClasses(), [
                                 'class' => "simplecolorpicker picker color",
                             ]) ?>
                </div>
            </li>
            <li class="control-group">
                <label>Напомнить клиенту за</label>
                <div class="controls">
                    <?= $form->field($model, 'hours_before', [
                        'template' => "{input}\nдо визита\n{error}"
                    ])->dropDownList(OrderHelper::getNotificationTimeList()) ?>
                </div>
            </li>
            <?php if (Yii::$app->user->identity->company->isMedCategory()): ?>
                <li class="control-group">
                    <label>Страховая компания</label>
                    <div class="controls">
                        <?= $form->field($model, 'insurance_company_id')
                            ->dropDownList(Insurance::map("insurance_company_id", "insuranceCompany.name"), [
                                     'prompt' => Yii::t('app', 'Unknown')
                                 ])
                                 ->label(false) ?>
                    </div>
                </li>
            <?php else: ?>
                <?= $form->field($model, 'insurance_company_id')->hiddenInput()
                         ->label(false) ?>
            <?php endif; ?>

            <?php if (Yii::$app->user->identity->company->show_referrer): ?>
                <li class="control-group">
                    <div class="controls">
                        <?php
                        $url = Url::to(Yii::$app->params['api_host'].'/v2/company/'.Yii::$app->user->identity->company_id.'/referrer?access-token='. Yii::$app->user->identity->getValidAccessToken());
                        $link = Html::a(
                            Yii::t('app', 'Add referrer'),
                            $url,
                            ['class' => 'btn left_space js-new-referrer pull-right']
                        );

                        echo $form->field(
                            $model,
                            'referrer_id',
                            ['template' => "{label}{$link}<div class='b-referrer_input'>{input}</div>{error}"]
                        )->widget(Select2::className(), [
                            'data'          => Referrer::map(),
                            'options'       => [
                                'prompt' => Yii::t('app', 'Unknown')
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]);
                        ?>
                    </div>
                </li>
            <?php endif; ?>

            <li class="control-group">
                <div class="controls">
                    <?php
                    $link = Html::a(
                            Yii::t('app', 'Add Source'),
                        '/customer/customer-source/new',
                            ['class' => 'btn left_space js-new_customer_source_link pull-right']
                    );

                    echo $form->field(
                        $model,
                        'customer_source_id',
                        ['template' => "{label}{$link}<div class='b-referrer_input'>{input}</div>{error}"]
                    )->widget(Select2::className(), [
                        'data'          => CustomerSource::map(),
                        'options'       => [
                            'prompt' => Yii::t('app', 'Unknown')
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);
                    ?>
                </div>
            </li>
        </ol>
    </div>
</div>

<?= $form->field($model, 'datetime')
         ->hiddenInput()
         ->label(false)
         ->error(false); ?>

<div class="row">
    <div class="col-sm-12">
        <?= $form->field($model, 'note')->textarea() ?>
    </div>
</div>