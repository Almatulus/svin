<?php

use core\models\division\Division;
use core\models\warehouse\Category;
use core\models\warehouse\Manufacturer;
use core\models\warehouse\ProductType;
use core\models\warehouse\ProductUnit;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \core\forms\warehouse\product\ProductCreateForm */
/* @var $form yii\widgets\ActiveForm */


$categoryLink = Html::a(Yii::t('app', 'Add category'), '/warehouse/category/new', [
    'id' => 'new_category_link',
    'class' => 'left_space v_middle nowrap stock_new_entity_link',
    'title' => Yii::t('app', 'Add category'),
    'data-model' => 'category',
    'data-title' => Yii::t('app', 'Category')
]);
$catTemplate = "{input}{$categoryLink}";
$manufacturerLink = Html::a(Yii::t('app', 'Add manufacturer'), '/warehouse/manufacturer/new', [
    'id' => 'new_manufacturer_link',
    'class' => 'left_space v_middle nowrap stock_new_entity_link',
    'title' => Yii::t('app', 'Add manufacturer'),
    'data-model' => 'manufacturer',
    'data-title' => Yii::t('app', 'Manufacturer')
]);
$manTemplate = "{input}{$manufacturerLink}";

$backUrl = Yii::$app->request->referrer;
if (!$backUrl) {
    $backUrl = ['index'];
}

$units = ProductUnit::map();
?>
<div class="product-form">

<?php $form = ActiveForm::begin([
    'fieldConfig' => ['options' => ['class' => '']],
    'options' => ['class' => 'simple_form new_product']
]); ?>

    <div class="column_row">
        <fieldset>
            <legend><?= Yii::t('app', 'General data') ?></legend>
            <ol>
                <li class="control-group string product_name">
                    <div class="controls">
                        <?= $form->field($model, 'name')->textInput(['class' => 'string options']) ?>
                    </div>
                </li>
                <li class="control-group">
                    <div class="controls">
                        <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList()) ?>
                    </div>
                </li>
                <li class="control-group string product_types">
                    <div class="controls">
                        <?php
                        $items = ProductType::map();
                        ?>
                        <?= $form->field($model, 'types', [
                            'inline' => true,
                            'wrapperOptions' => ['class' => 'checkbox_group inline_block']
                        ])->checkboxList($items, ['itemOptions' => ['class' => '',]]) ?>
                    </div>
                </li>
                <li class="control-group select product_category_id">
                    <div class="controls">
                        <?= $form->field($model, 'category_id', ['inputTemplate' => $catTemplate])->dropDownList(Category::map(), ['prompt' => '']) ?>
                    </div>
                </li>
                <li class="control-group string product_purchase_price">
                    <div class="controls">
                        <?= $form->field($model, 'purchase_price')->textInput(['style' => 'width: 89px']) ?>
                    </div>
                </li>
                <li class="control-group string product_price">
                    <div class="controls">
                        <?= $form->field($model, 'price')->textInput(['style' => 'width: 89px']) ?>
                    </div>
                </li>
                <li class="control-group string product_vat">
                    <div class="controls">
                        <?= $form->field($model, 'vat')->textInput(['style' => 'width: 89px']) ?>
                    </div>
                </li>
                <li class="control-group select product_unit_id">
                    <div class="controls">
                        <?= $form->field($model, 'unit_id')->dropDownList($units) ?>
                    </div>
                </li>
                <li class="control-group string product_quantity">
                    <div class="controls">
                        <?= $form->field($model, 'quantity')->textInput(['style' => 'width: 89px']) ?>
                    </div>
                </li>
            </ol>
            <legend><?= Yii::t('app', 'Extended details') ?></legend>
            <ol>
                <li class="control-group string product_min_quantity">
                    <div class="controls">
                        <?= $form->field($model, 'min_quantity')->textInput(['style' => 'width: 89px']) ?>
                    </div>
                </li>
                <li class="control-group string product_manufacturer_id">
                    <div class="controls">
                        <?= $form->field($model, 'manufacturer_id', ['inputTemplate' => $manTemplate])->dropDownList(Manufacturer::map(), ['prompt' => '']) ?>
                    </div>
                </li>
                <li class="control-group string product_sku">
                    <div class="controls">
                        <?= $form->field($model, 'sku') ?>
                    </div>
                </li>
                <li class="control-group string product_barcode">
                    <div class="controls">
                        <?= $form->field($model, 'barcode') ?>
                    </div>
                </li>
                <li class="control-group string product_description">
                    <div class="controls">
                        <?= $form->field($model, 'description')->textarea() ?>
                    </div>
                </li>
            </ol>
        </fieldset>
    </div>

    <div class="form-actions">
        <div class="with-max-width">
            <div class="pull_right cancel-link">
                <?= Html::a('Отмена', $backUrl) ?>
            </div>
            <button class="btn btn-primary" type="submit">
                <span class="icon sprite-add_customer_save"></span>
                <?= Yii::t('app', 'Save') ?>
            </button>
            <button class="btn btn-default" type="submit"  name="action" value="add-another">
                <?= Yii::t('app', 'Save and add another') ?>
            </button>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
