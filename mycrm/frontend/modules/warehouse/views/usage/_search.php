<?php

use core\models\Staff;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\UsageSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="usage-search">
    <?php $form = ActiveForm::begin([
        'action'  => ['index'],
        'method'  => 'get',
        'options' => ['data-pjax' => true, 'class' => 'details-row'],
    ]); ?>
    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, 'start', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'From date') . '</span>{input}</div>',
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'end', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'To date') . '</span>{input}</div>',
            ])->widget(DatePicker::class, [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-2">
            <?= Html::activeTextInput($model, 'name',
                ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Find usage')]); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'staff_id')->widget(Select2::className(), [
                'data' => Staff::map(),
                'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Staff'), 'class'=>'input'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'language' => 'ru',
                ],
                'size' => 'sm',
                'showToggleAll' => false,
            ])->label(false);?>
        </div>
        <div class="col-sm-4 right-buttons">
            <?= Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary']); ?>
            <?= Html::a(Yii::t('app', 'Add usage'), ['create'], ['class' => 'btn btn-primary pull-right']) ?>
            <div class="customer-actions inline_block pull-right right_space">
                <div class="dropdown">
                    <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
                        Действия <b class="caret"></b>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app',
                                    'Delete selected'), '#', [
                                'class' => 'js-cancel-usages disabled'
                            ]) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<?php


$js = <<<JS
$(function () {
    var gridView = $('#usages');
    
    gridView.on('change', 'input:checkbox', function(e) {
        let keys = gridView.yiiGridView('getSelectedRows');
        if (keys.length > 0) {
            $('.js-delete-usages').removeClass('disabled');
        } else {
            $('.js-delete-usages').addClass('disabled');
        }
    });

    $('.js-delete-usages').click(handleDeleteClick);
    
    function handleDeleteClick(e) {
        if (gridView.yiiGridView('getSelectedRows').length > 0) {
            if(confirm("Вы уверены что хотите удалить данные записи?") === true) {
                deleteUsages();
            }
        }
    }
    
    function deleteUsages() {
        let deleteURL = 'batch-delete';
        let data = { 'selected': gridView.yiiGridView('getSelectedRows') };
        $.post(deleteURL, data, function(response) {
            let message = "<b>Удалено: " + response.deleted + '</b><br><br>';
            message += response.errors.map(function(msg) { return '<p class="red">' + msg + '</p>'}).join('');
            alertMessage(message, function() {
                location.reload();
            });
        });
    }
});
JS;

$this->registerJs($js);