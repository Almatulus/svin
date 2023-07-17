<?php

use core\forms\customer\statistic\StatisticStaff;
use core\models\warehouse\Category;
use kartik\date\DatePicker;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Sales analysis');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label' => $this->title
];
$this->params['bodyID'] = 'summary';

$totalIncome = $totalCost = 0;
$products = $dataProvider->models;
foreach ($products as $key => $product) {
    $totalIncome += $product->income;
    $totalCost += $product->totalCost;
}
?>

<?= $this->render('/common/_tabs') ?>

<div class="sale-analysis">

    <?php $form = ActiveForm::begin([
        'action' => ['analysis'],
        'method' => 'get',
    ]); ?>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'start_date', [
                'template' => "<div class='input-group'><span class='input-group-addon'>"
                            . $model->getAttributeLabel('start_date')
                            . "</span>{input}</div>",
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ])?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'end_date', [
                'template' => "<div class='input-group'><span class='input-group-addon'>"
                            . $model->getAttributeLabel('end_date')
                            . "</span>{input}</div>",
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ])?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'category_id')->dropDownList(Category::map(), [
                'prompt' => Yii::t('app', 'All Categories')
            ])->label(false) ?>
        </div>
        <div class="col-md-3">
            <?php
                $items = StatisticStaff::getOwnCompanyStaffList();
                $items[0] = 'продажа без сотрудника';
            ?>
            <?= $form->field($model, 'staff_id')->dropDownList($items, [
                'prompt' => Yii::t('app', 'All Staff')
            ])->label(false) ?>
        </div>
        <div class="col-md-12">
            <div class="form-group pull-right">
                <?= Html::a(Yii::t('app', 'Export'), array_merge(['export-analysis'], Yii::$app->request->queryParams),
                    ['class' => 'btn btn-default']
                ) ?>
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
    <?php $form->end(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['class' => ''],
        'showFooter' => true,
        'columns' => [
            'product.sku',
            'product.name',
            'quantity',
            [
                'attribute' => 'product.unit.name',
                'label' => Yii::t('app', 'Unit')
            ],
            [
                'attribute' => 'purchase_price',
                'format' => 'decimal'
            ],
            [
                'attribute' => 'extraCharge',
                'format' => 'decimal'
            ],
            [
                'attribute' => 'extraChargeRate',
                'format' => 'percent'
            ],
            [
                'attribute' => 'price',
                'format' => 'decimal'
            ],
            [
                'attribute' => 'totalCost',
                'format' => 'decimal',
                'footer' => Yii::$app->formatter->asDecimal($totalCost)
            ],
            [
                'attribute' => 'income',
                'format' => 'decimal',
                'footer' => Yii::$app->formatter->asDecimal($totalIncome)
            ],
            [
                'attribute' => 'sale.payment.label',
                'label' => Yii::t('app', 'Payment')
            ],
            [
                'attribute' => 'sale.staff.name',
                'label' => Yii::t('app', 'Staff ID')
            ],
            [
                'attribute' => 'sale.companyCustomer.customer.fullName',
                'label' => Yii::t('app', 'Customer')
            ]
        ]
    ]);
    ?>
</div>