<?php

use core\helpers\customer\CustomerLoyaltyHelper;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerLoyalty;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerLoyalty */
/* @var $form yii\widgets\ActiveForm */

$style = '';
?>

<div class="customer-loyalty-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="js-form-mode">
        <?= $form->field($model, 'mode')->dropDownList(CustomerLoyaltyHelper::getModeLabels(), ['id' => 'js-form-mode']) ?>
    </div>
    <div class="js-form-mode">
        <?= $form->field($model, 'event')->dropDownList(CustomerLoyaltyHelper::getEventLabels(), ['id' => 'js-form-event']) ?>
    </div>
    <div class="js-form-amount">
        <?= $form->field($model, 'amount')->textInput(['id' => 'js-form-amount', 'style' => 'width:80px']) ?></div>

    <?php if ($model->mode == CustomerLoyalty::MODE_ADD_DISCOUNT) $style = ''; else $style = 'display:none'; ?>
    <div style="<?= $style ?>" class="js-form-discount">
        <?= $form->field($model, 'discount', [
            'inputTemplate' => '<div class="input-group">{input}<span class="input-group-addon" style="display:inline-block;width:20px;font-size:14px">%</span></div>'
        ])->textInput(['id' => 'js-form-discount', 'style' => 'width:60px']) ?>
    </div>

    <?php if ($model->isCategoryMode()) $style = ''; else $style = 'display:none'; ?>
    <div style="<?= $style ?>" class="js-form-category">
        <?= $form->field($model, 'category_id')->widget(Select2::className(), [
            'id' => 'js-form-category',
            'data' => ArrayHelper::map(CustomerCategory::find()->company()->all(), 'id', 'name'),
            'options' => ['multiple' => false, 'placeholder' => Yii::t('app', '-- Select Category --')],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'showToggleAll' => false,
            'size' => 'sm'
        ]); ?>
    </div>

    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>
