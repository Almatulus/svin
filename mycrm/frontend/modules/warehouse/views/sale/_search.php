<?php

use core\models\Staff;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\SaleSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sale-search">
    <div class="column_row row">
        <div class="col-sm-12">
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
                'options' => ['data-pjax' => true],
            ]); ?>
            <div class="sale-search-names">
                <div class="row">
                    <div class="col-md-2">
                        <?= $form->field($model, 'name')
                            ->textInput(['placeholder' => Yii::t('app', 'Find sale')])
                            ->label(false); ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'staff_id')->widget(Select2::className(), [
                            'data' => Staff::map(),
                            'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Staff'), 'class' => 'input'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'language' => 'ru',
                            ],
                            'size' => 'sm',
                            'showToggleAll' => false,
                        ])->label(false); ?>
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
                    <div class="col-sm-4 right-buttons">
                        <?= Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary']); ?>
                        <?= Html::a(Yii::t('app', 'Add sale'), ['create'], ['class' => 'btn btn-primary pull-right']) ?>
                        <div class="customer-actions inline_block pull-right right_space">
                            <div class="dropdown">
                                <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
                                    Действия <b class="caret"></b>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app',
                                                'Delete selected'), '#', [
                                            'class' => 'js-delete-sales disabled'
                                        ]) ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>

        </div>

    </div>
</div>
