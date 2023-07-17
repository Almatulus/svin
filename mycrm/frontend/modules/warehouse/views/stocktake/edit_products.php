<?php

use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Stocktake */


$this->title = Yii::t('app', 'Stocktakes');
$this->params['breadcrumbs'][]    = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 
    'label' => $this->title, 
    'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Count products');

?>

<?= $this->render('/common/_tabs') ?>

<?= $this->render('_steps', ['model' => $model]) ?>

<div class="column_row">
    <h2><?= $model->title ?></h2>
</div>

<?php
$form = ActiveForm::begin([
    'id' => 'stocktake_form',
    'fieldConfig' => [
        'options' => ['class' => ''],
    ],
    'options' => ['class' => 'simple_form']
]);

DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper_stocktake', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-products', // required: css class selector
    'widgetItem' => '.dynamic-product', // required: css class
    'limit' => 50, // the maximum times, an element can be cloned (default 999)
    'min' => 1, // 0 or 1 (default 1)
    'insertButton' => '.add-product', // css class
    'deleteButton' => '.remove-product', // css class
    'model' => $products[0],
    'formId' => $form->getId(),
    'formFields' => [
        'product_id',
        'actual_stock_level',
    ],
]); 
?>

<div class="column_row data_table">
    <table class="data_table">
        <thead>
            <tr>
                <th><?= Yii::t('app', 'Name') ?></th>
                <th><?= Yii::t('app', 'Product type') ?></th>
                <th><?= Yii::t('app', 'Unit') ?></th>
                <th><?= Yii::t('app', 'Recorded stock level') ?></th>
                <th><?= Yii::t('app', 'Actual stock level') ?></th>
                <th><?= Yii::t('app', 'Products difference') ?></th>
                <th><?= Yii::t('app', 'Estimated variance value') ?></th>
                <th class="text-center"><?= Yii::t('app', 'Delete') ?></th>
            </tr>
        </thead>
        <tbody class="container-products">
            <?php foreach($products as $key => $product) { ?>
                <tr class="dynamic-product" data-purchase-price="<?= $product->purchase_price ?>">
                    <td>
                        <?php
                        $cellClass = "";
                        if (!$product->isNewRecord) {
                            if ($product->balance > 0 || $product->balance < 0) {
                                $cellClass = "negative";
                            } else {
                                $cellClass = "positive";
                            }
                            echo $form->field($product, "[{$key}]id")->hiddenInput()->label(false);
                        }
                        ?>
                        <?= $form->field($product, "[{$key}]product_id")->hiddenInput()->label(false) ?>
                        <?= $product->product->name ?>        
                    </td>
                    <td><?= $product->product->getTypesTitle('<br>') ?></td>
                    <td><?= $product->product->unit->name ?></td>
                    <td class="recorded-stock-level-cell"><?= $product->recorded_stock_level ?></td>
                    <td>
                        <?= $form->field($product, "[{$key}]actual_stock_level")->textInput([
                            'class' => 'actual_stock_level-cell',
                            'style' => 'width: 55px'
                        ])->label(false) ?>
                    </td>
                    <td class="balance-cell <?= $cellClass ?>"><?= $product->balanceText ?></td>
                    <td class="estimated_variance-cell <?= $cellClass ?>"><?= $product->estimatedVarianceText ?></td>
                    <td class="text-center">
                        <a href="javascript:void(0);" class="remove-product">
                            <span class="glyphicon glyphicon-trash"></span>
                        </a>
                    </td>
                <tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php DynamicFormWidget::end(); ?>

<div class="form-actions">
    <div class="with-max-width">
        <button class="btn btn-primary" type="submit">
            <?= Yii::t('app', 'Complete stocktake') ?>
        </button>
        <?= Html::a(Yii::t('app', 'Back'), ['update', 'id' => $model->id], ['class' => 'btn']) ?>
        <?= Html::a(Yii::t('app', 'Cancel'), ['delete', 'id' => $model->id], [
            'class' => 'nowrap',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to cancel this stocktake?'),
                'method'  => 'post',
                'form'    => 'noform'
            ],
        ]) ?>
    </div>
</div>

<?php $form->end() ?>

<?php 

$js = <<<JS
    $('.actual_stock_level-cell').change(function(e) {
        var row = $(this).closest('tr');
        var actual_stock_level = $(this).val();
        var recorded_stock_level = row.find('.recorded-stock-level-cell').text();
        var purchase_price = row.data('purchase-price');

        var balance = actual_stock_level - recorded_stock_level;
        var estimated_variance = (balance * purchase_price).toFixed(2);
        var className = "positive";
        if (balance > 0) {
            balance = "+" + balance;
            if (estimated_variance > 0) {
                estimated_variance = "+" + estimated_variance;
            }
            className = "negative";
        } else if (balance < 0) {
            className = "negative";
        }

        var balanceCell = row.find('.balance-cell');
        balanceCell.text(balance);
        balanceCell.attr('class', "balance-cell " + className);

        var estimatedVarianceCell = row.find('.estimated_variance-cell');
        estimatedVarianceCell.text(estimated_variance + " ₸");
        estimatedVarianceCell.attr('class', "estimated_variance-cell " + className);
    });
JS;

$this->registerJs($js);
?>