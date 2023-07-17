<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Stocktake */


$this->title = Yii::t('app', 'Stocktakes');
$this->params['breadcrumbs'][]    = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 
    'label' => $this->title, 
    'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Stock levels summary');

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
?>

<div class="column_row">
    <?= DetailView::widget([
        'options' => ['class' => 'info_table compact vertical'],
        'template' => '<tr><td class="key">{label}</td><td class="value">{value}</td></tr>',
        'model' => $model,
        'attributes' => [
            'accurateProductsCount',
            'productsWithShortageCount',
            'productsWithSurplusCount'
        ],
    ]) ?>
</div>

<div class="column_row data_table">
    <table class="data_table">
        <thead>
            <tr>
                <th></th>
                <th><?= Yii::t('app', 'Name') ?></th>
                <th><?= Yii::t('app', 'Product type') ?></th>
                <th><?= Yii::t('app', 'Unit') ?></th>
                <th><?= Yii::t('app', 'Purchase price') ?></th>
                <th><?= Yii::t('app', 'Recorded stock level') ?></th>
                <th><?= Yii::t('app', 'Actual stock level') ?></th>
                <th><?= Yii::t('app', 'Products difference') ?></th>
                <th><?= Yii::t('app', 'Estimated variance value') ?></th>
            </tr>
        </thead>
        <tbody class="container-products">
            <?php foreach($products as $key => $product) { ?>
                <tr class="dynamic-product"  data-actual-stock-level="<?= $product->actual_stock_level ?>">
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
                    $product->apply_changes = true;
                    ?>
                    <?= $form->field($product, "[{$key}]product_id")->hiddenInput()->label(false) ?>
                    <td>
                        <?= $form->field($product, "[{$key}]apply_changes")->checkbox([], false)->label(false) ?>        
                    </td>
                    <td><?= $product->product->name ?></td>
                    <td><?= $product->product->getTypesTitle('<br>') ?></td>
                    <td><?= $product->product->unit->name ?></td>
                    <td>
                        <?= $form->field($product, "[{$key}]purchase_price")->textInput([
                            'class' => 'purchase_price-cell',
                            'style' => 'width: 55px'
                        ])->label(false) ?>
                    </td>
                    <td class="recorded-stock-level-cell"><?= $product->recorded_stock_level ?></td>
                    <td><?= $product->actual_stock_level ?></td>
                    <td class="<?= $cellClass ?>"><?= $product->balanceText ?></td>
                    <td class="estimated_variance-cell <?= $cellClass ?>"><?= $product->estimatedVarianceText ?></td>
                <tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="form-actions">
    <div class="with-max-width">
        <?= Html::submitButton(Yii::t('app', 'Apply correction and finish'), [
            'class' => "btn btn-primary",
            'data' => ['confirm' => 'Вы уверены, что хотите закончить инвентаризацию и обновить уровни запасов выбранных товаров?'],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Finish without correction'), ['execute', 'id' => $model->id], ['class' => 'btn']); ?>
        <?= Html::a(Yii::t('app', 'Back'), ['edit-products', 'id' => $model->id], ['class' => 'btn']); ?>
    </div>
</div>

<?php $form->end() ?>

<?php 

$js = <<<JS
    $('.purchase_price-cell').change(function(e) {
        var row = $(this).closest('tr');
        var purchase_price = $(this).val();
        var recorded_stock_level = row.find('.recorded-stock-level-cell').text();
        var actual_stock_level = row.data('actual-stock-level');

        var balance = actual_stock_level - recorded_stock_level;
        var estimated_variance = (balance * purchase_price).toFixed(2);

        var estimatedVarianceCell = row.find('.estimated_variance-cell');
        estimatedVarianceCell.text(estimated_variance + " ₸");
    });
JS;

$this->registerJs($js);
?>