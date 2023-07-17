<?php
/**
 * @var $form ActiveForm
 */

use core\forms\medCard\MedCardTabForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

$model = new MedCardTabForm();

?>
<div class="row" id="js-med-card-tab-services">
    <div class="col-md-12">
        <label class="control-label"><?= Yii::t('app', 'Services') ?></label>
        <div id="med_card_services_tabbed_table" class="tabbed-table"
             style='display: none'>
            <div class="tabs">
                <a href="#" class="tabs-tab active" data-target="services">
                    <span class="icon right_space sprite-calendar_event"></span>
                    <span><?= Yii::t('app', 'Services') ?></span>
                </a>
            </div>
            <div class="table-wrapper">
                <div id="services" class="tabbed-table">
                    <?= Html::tag("table", '',
                        [
                            'id'    => 'med_card_services_table',
                            'class' => 'data_table table-services',
                            'style' => 'background-color: #ddd'
                        ]
                    ); ?>
                </div>
            </div>
        </div>
        <?php
        echo $form->field($model, '[]services')->widget(Select2::className(), [
            'size'          => 'sm',
            'options'       => [
                'prompt' => Yii::t('app', 'Select service')
            ],
            'pluginEvents'  => [
                'select2:select' => "medCardServicesSelectEvent",
                'select2:close'  => "medCardServicesCloseEvent"
            ],
            'pluginOptions' => [
                'allowClear'         => true,
                'minimumInputLength' => 0,
                'language'           => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                ],
                'ajax'               => [
                    'url'      => Url::to('/division/service/search'),
                    'dataType' => 'json',
                    'data'     => new JsExpression('function(params) { return {name:params.term}; }')
                ],
                'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                'templateResult'     => new JsExpression('function(data) { return data.name; }'),
                'templateSelection'  => new JsExpression('function (data) { return data.name; }'),
            ],
        ])->label(false);
        echo Html::button(Yii::t('app', 'Add service'), [
            'id'    => 'js-med-card-add-service',
            'class' => 'btn btn-sm btn-default pull-right',
            'style' => 'display: none'
        ]);
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'price', [
            'errorOptions' => ['style' => 'margin: 0'],
            'inputOptions' => ['value' => 0]
        ])
            ->textInput(['readonly' => true])
            ->label(Yii::t('app', 'Order total price, currency')) ?>
    </div>
</div>
<hr>
