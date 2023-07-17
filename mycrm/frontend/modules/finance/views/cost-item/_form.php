<?php

use core\models\division\Division;
use core\helpers\finance\CompanyCostItemHelper;
use core\models\finance\CompanyCostItem;
use core\models\finance\CompanyCostItemCategory;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCostItem */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-cost-item-form">

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'options' => ['tag' => 'li', 'class' => 'control-group'],
            'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
            'wrapperOptions' => ['class' => 'controls'],
        ],
        'options' => ['class' => 'simple_form'],
    ]); ?>

    <ol>
        <?= $form->field($model, 'name')->textInput(['class' => 'string options', 'maxlength' => true]) ?>
        <?= $form->field($model, 'type')->dropDownList(CompanyCostItemHelper::getTypeLabels()) ?>
        <?= $form->field($model, 'comments')->textarea(['rows' => 6, 'class' => 'string options', 'maxlength' => true]) ?>
        <?= $form->field($model, 'category_id')->widget(Select2::className(), [
            'options'       => [
                'placeholder' => Yii::t('app', 'Enter the name and select from list'),
            ],
            'data'          => CompanyCostItemCategory::map(),
            'size'          => 'sm',
            'pluginOptions' => [
                'width'      => '240px',
                'allowClear' => true
            ]
        ]) ?>
        <?= $form->field($model, 'divisions')->widget(Select2::className(), [
            'options'       => [
                'placeholder' => Yii::t('app', 'Enter the name and select from list'),
                'multiple' => true
            ],
            'data'          => Division::getOwnDivisionsNameList(),
            'size'          => 'sm',
            'pluginOptions' => [
                'width'      => '240px',
                'allowClear' => true
            ]
        ]) ?>
    </ol>

    <div class="form-actions">
        <div class="pull_right cancel-link">
            <?= Html::a('Отмена', Yii::$app->request->referrer) ?>
        </div>
        <button class="btn btn-primary" name="commit" type="submit">
            <span class="icon sprite-add_customer_save"></span>Сохранить
        </button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
