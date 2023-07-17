<?php

use core\models\division\DivisionService;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $service core\models\finance\PayrollService */
/* @var $index integer */
?>

<tr class="dynamic-service">
    <td>
        <?php
        if (! $service->isNewRecord) {
            echo Html::activeHiddenInput($service, "[{$index}]id");
        }
        ?>
        <?= $form->field($service, "[{$index}]division_service_id", ['template' => "{label}<br/>{input}<br/>{error}"])
            ->dropDownList(DivisionService::getOwnCompanyDivisionServicesList(),[
                'prompt' => Yii::t('app', 'Select service')
            ]) ?>
    </td>
    <td><?= $form->field($service, "[{$index}]service_value", ['template' => "{label}<br/>{input}<br/>{error}"])->textInput(['maxlength' => true]) ?></td>
    <td>
        <?= $form->field($service, "[{$index}]service_mode", [
            'template' => "{label}<br/>{input}<br/>{error}"
        ])->dropDownList(\core\models\finance\Payroll::getModeLabels()) ?>
    </td>
    <td><a href="javascript:void(0);" class="remove-service"><span class="glyphicon glyphicon-trash"></span></a></td>
</tr>