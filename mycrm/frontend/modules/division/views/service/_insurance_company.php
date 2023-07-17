<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \core\models\InsuranceCompany */
/* @var $index integer */


?>

<tr class="dynamic-insurance-company">
    <td width="200px">
        <?php
        if (!$model->isNewRecord) {
            echo Html::activeHiddenInput($model, "[{$index}]id");
        }
        ?>
        <?= $form->field($model, "[{$index}]insurance_company_id", ['template' => "{input}\n{error}"])
            ->dropDownList(
                \core\models\InsuranceCompany::map(),
                ['prompt' => Yii::t('app', "Select insurance company")]
            );
        ?>
    </td>
    <td>
        <?= $form->field($model, "[{$index}]price", ['template' => "{input}\n{error}"])->textInput(['style' => 'width:50px', 'class' => 'insurance-company-price']) ?>
    </td>
    <td class="text-center">
        <a href="javascript:void(0);" class="remove-insurance-company"><span class="glyphicon glyphicon-trash"></span>
        </a>
    </td>
</tr>