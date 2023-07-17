<?php

use core\models\warehouse\UsageProduct;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model UsageProduct */
/* @var $index integer */

if (!$model->quantity) { $model->quantity = 1; }
?>

<tr class="dynamic-product">
    <td width="200px">
        <?php
            if (!$model->isNewRecord) {
                echo Html::activeHiddenInput($model, "[{$index}]id");
            }
        ?>
        <?= $form->field($model, "[{$index}]product_id", ['template' => "{input}\n{error}"])
                ->widget(Select2::className(), [
                    'initValueText' => isset($model->product) ? $model->product->name : '',
                    'options' => ['placeholder' => Yii::t('app', 'Select product')],
                    'pluginOptions' => [
                        'allowClear' => false,
                        'minimumInputLength' => 3,
                        'ajax' => [
                            'url' => Url::to(['/warehouse/product/search']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {search:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(material) {return material.text; }'),
                        'templateSelection' => new JsExpression('function (material) {return material.text; }'),
                        'size' => 'sm',
                    ],
                    'size' => 'sm',
                    'pluginEvents' => [
                        "select2:select" => 'function(evt) {
                            selectServiceProduct(evt);
                        }',
                    ]
                ]
            );
        ?>
    </td>
    <td class="product-unit"><?= isset($model->product) ? $model->product->unit->name : ''?></td>
    <td>
        <?= $form->field($model, "[{$index}]quantity", ['template' => "{input}\n{error}"])->textInput(['style' => 'width:50px', 'class' => 'product-quantity']) ?>
    </td>
    <td class="text-center">
        <a href="javascript:void(0);" class="remove-product"><span class="glyphicon glyphicon-trash"></span>
        </a>
    </td>
</tr>