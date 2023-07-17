<?php

use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;

DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper_products', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-products', // required: css class selector
    'widgetItem' => '.dynamic-product', // required: css class
    'limit' => 50, // the maximum times, an element can be cloned (default 999)
    'min' => 0, // 0 or 1 (default 1)
    'insertButton' => '.add-product', // css class
    'deleteButton' => '.remove-product', // css class
    'model' => $products[0],
    'formId' => $form->getId(),
    'formFields' => [
        'product_id',
        'quantity',
    ],
]); ?>

<div class="data_table no_hover">
    <table>
        <thead>
        <tr>
            <th><?= Yii::t('app', 'Product') ?></th>
            <th><?= Yii::t('app', 'Unit') ?></th>
            <th><?= Yii::t('app', 'Quantity') ?></th>
            <th><?= Yii::t('app', 'Delete') ?></th>
        </tr>
        </thead>
        <tbody class="container-products">
            <?php
            foreach ($products as $index => $product): 
                echo $this->render("_product", [
                    'form' => $form,
                    'model' => $product,
                    'index' => $index
                ]);
            endforeach;
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td><?= Html::button(Yii::t('app', 'Add'), ['class' => 'btn add-product'])?></td>
                <td class="summary right_text" colspan="5">
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<?php DynamicFormWidget::end(); ?>