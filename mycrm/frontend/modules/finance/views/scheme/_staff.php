<?php

use kartik\date\DatePicker;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $staff core\models\finance\PayrollStaff */
/* @var $index integer */

?>
<div class="row dynamic-staff">
    <div class="col-sm-4">
        <?php
        if (! $staff->isNewRecord) {
            echo Html::activeHiddenInput($staff, "[{$index}]id");
        }
        ?>
        <?= $form->field($staff, "[{$index}]staff_id", ['template' => "{input}\n{error}"])
            ->dropDownList(\core\models\Staff::getOwnCompanyStaffList(),
                ['prompt' => Yii::t('app', 'Select staff')]) ?>
    </div>
    <div class="col-sm-2">
        начиная с
    </div>
    <div class="col-sm-5">
        <?= $form->field($staff, "[{$index}]started_time", ['template' => "{input}\n{error}"])
            ->widget(DatePicker::className(), [
                'options' => ['placeholder' => Yii::t('app', 'Select date')],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ]
            ]); ?>
    </div>
    <div class="col-sm-1">
        <a href="javascript:void(0);" class="remove-staff pull-right">
            <span class="glyphicon glyphicon-trash"></span>
        </a>
    </div>

</div>