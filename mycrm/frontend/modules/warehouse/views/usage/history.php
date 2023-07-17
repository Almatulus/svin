<?php

/** @var $searchModel \core\models\warehouse\UsageHistorySearch */

use kartik\date\DatePicker;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/** @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Usage history');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label'    => $this->title,
    'url'      => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="sale-index">

    <div class="usage-search">
        <?php $form = ActiveForm::begin([
            'action'  => ['history'],
            'method'  => 'get',
            'options' => ['data-pjax' => true, 'class' => 'details-row'],
        ]); ?>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($searchModel, "product_id", ['template' => "{input}\n{error}"])
                    ->widget(Select2::className(), [
                            'initValueText' => isset($searchModel->product) ? $searchModel->product->name : '',
                            'options'       => ['placeholder' => Yii::t('app', 'Select product')],
                            'pluginOptions' => [
                                'allowClear'         => false,
                                'minimumInputLength' => 1,
                                'ajax'               => [
                                    'url'      => Url::to(['product/search']),
                                    'dataType' => 'json',
                                    'data'     => new JsExpression('function(params) { return {search:params.term}; }')
                                ],
                                'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                                'templateResult'     => new JsExpression('function(material) {return material.text; }'),
                                'templateSelection'  => new JsExpression('function (material) {return material.text; }'),
                                'size'               => 'sm',
                            ],
                            'size'          => 'sm',
                        ]
                    );
                ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($searchModel, 'start', [
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
            <div class="col-sm-3">
                <?= $form->field($searchModel, 'end', [
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
            <div class="col-sm-1">
                <?= Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary']); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php
    if ($searchModel->product) {
        echo \yii\widgets\DetailView::widget([
            'model'      => $searchModel->product,
            'attributes' => [
                'name',
                'quantity',
                'price'
            ]
        ]);
    }
    ?>
    <?= GridView::widget([
        'dataProvider'    => $dataProvider,
        'summary'         => \core\helpers\HtmlHelper::getSummary(),
        'columns'         => [
            [
                'attribute' => 'use.created_at',
                'format'    => 'datetime',
                'footer'    => 'Итого:'
            ],
            [
                'attribute'   => 'quantity',
                'format'      => 'decimal',
                'footer'      => \Yii::$app->formatter->asDecimal($dataProvider->query->sum('quantity')),
                'pageSummary' => true
            ]
        ],
        'showFooter'      => true,
        'showPageSummary' => true
    ]); ?>

</div>
