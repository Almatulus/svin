<?php

use core\models\finance\CompanyContractor;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\UsageSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="delivery-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="column_row row details-row">
        <div class="col-sm-2">
            <?= Html::activeTextInput($model, 'name',
                ['placeholder' => Yii::t('app', 'Find delivery'), 'class' => 'form-control']); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'contractor_id')->widget(Select2::className(), [
                'data'          => CompanyContractor::map(),
                'options'       => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Contractors')],
                'pluginOptions' => [
                    'allowClear' => true,
                    'language'   => 'ru',
                ],
                'size'          => 'sm',
                'showToggleAll' => false,
            ])->label(false) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'from_date')->widget(\kartik\date\DatePicker::className(), [
                'value' => (new DateTime())->modify('-1 month')->format('Y-m-d H:i:s'),
                'options' => ['class' => 'right_space', 'placeholder' => Yii::t('app', 'From')],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
            ])->label(false); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'to_date')->widget(\kartik\date\DatePicker::className(), [
                'value' => (new DateTime())->format('Y-m-d H:i:s'),
                'options' => ['class' => 'right_space', 'placeholder' => Yii::t('app', 'To')],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
            ])->label(false); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'created_from_date')->widget(\kartik\date\DatePicker::className(), [
                'value' => (new DateTime())->modify('-1 month')->format('Y-m-d H:i:s'),
                'options' => ['class' => 'right_space', 'placeholder' => Yii::t('app', 'Created From')],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
            ])->label(false); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'created_to_date')->widget(\kartik\date\DatePicker::className(), [
                'value' => (new DateTime())->format('Y-m-d H:i:s'),
                'options' => ['class' => 'right_space', 'placeholder' => Yii::t('app', 'Created To')],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
            ])->label(false); ?>
        </div>
        <div class="col-sm-12 right-buttons">
            <?= Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary']); ?>
            <?= Html::a(Yii::t('app', 'Add delivery'), ['create'], ['class' => 'btn btn-primary pull-right']) ?>
            <div class="customer-actions inline_block pull-right right_space">
                <div class="dropdown">
                    <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
                        Действия <b class="caret"></b>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete selected'), '#', [
                                'class' => 'js-delete-deliveries disabled'
                            ]) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
