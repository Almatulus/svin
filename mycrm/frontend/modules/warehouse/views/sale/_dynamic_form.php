<?php

use core\models\warehouse\Sale;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\helpers\Json;

DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper_products', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-products', // required: css class selector
    'widgetItem' => '.dynamic-product', // required: css class
    'min' => 1, // 0 or 1 (default 1)
    'insertButton' => '.add-product', // css class
    'deleteButton' => '.remove-product', // css class
    'model' => $products[0],
    'formId' => $form->getId(),
    'formFields' => [
        'product_id',
        'quantity',
        'price',
    ],
]); ?>

<div class="data_table column_row">
    <table>
        <thead>
        <tr>
            <th><?= Yii::t('app', 'Name') ?></th>
            <th><?= Yii::t('app', 'Unit') ?></th>
            <th><?= Yii::t('app', 'Quantity') ?></th>
            <th><?= Yii::t('app', 'Price') ?></th>
            <th><?= Yii::t('app', 'Discount') ?></th>
            <th><?= Yii::t('app', 'Sum') ?></th>
            <th><?= Yii::t('app', 'Stock level') ?></th>
            <th><?= Yii::t('app', 'Delete') ?></th>
        </tr>
        </thead>
        <tbody class="container-products">
            <?php
            $items = [];
            foreach ($products as $index => $product):
                $items[$index] = $product->attributes;
                if (isset($product->product)) {
                    $items[$index]['vat'] = $product->product->vat;
                }
                echo $this->render("_product", [
                    'form' => $form,
                    'model' => $product,
                    'index' => $index
                ]);
            endforeach;
            $items = htmlspecialchars(Json::encode($items));
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td><?= Html::button(Yii::t('app', 'Add'), ['class' => 'btn add-product'])?></td>
                <td class="summary right_text" colspan="6">
                    <span class="right_space"><?= Yii::t('app', 'Total') ?></span>
                    <span class="bigger_2">
                        <span class="products-total"><?= Sale::getSaleData($products)['total'] ?></span>
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<div id="model-products" data-products="<?= $items ?>"></div>

<?php DynamicFormWidget::end(); ?>