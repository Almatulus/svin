<?php

use core\models\finance\CompanyCostItem;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCostItemCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-cost-item-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cost_items')->widget(Select2::className(), [
        'options'       => [
            'placeholder' => Yii::t('app', 'Enter the name or select from list'),
            'multiple' => true
        ],
        'data'          => CompanyCostItem::map(),
        'size'          => 'sm',
        'pluginOptions' => [
            'width'      => '240px',
            'allowClear' => true
        ]
    ])
    ?>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'),
            ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
