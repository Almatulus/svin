<?php

use core\helpers\customer\RequestTemplateHelper;
use core\models\customer\CustomerRequestTemplate;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $keys core\models\customer\CustomerRequestTemplate[] */

$this->title = Yii::t('app', 'Request Templates');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $this->title
];
?>
<div class="customer-request-settings">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($keys); ?>

    <div class="col-md-7">

        <?php foreach ($keys as $key => $value): ?>
            <?php /* @var $value CustomerRequestTemplate */ ?>
            <div class="row">
                <div class="col-sm-12 bordered-area">
                    <?= $form->field($value, "[{$key}]is_enabled")->checkbox([
                        'label'        => $value->description,
                        'labelOptions' => ['data-toggle' => 'collapse', 'data-target' => "#request-{$key}"]
                    ]) ?>
                    <?php
                    $class = "collapse";
                    if ($value->is_enabled) {
                        $class = "collapse in";
                    }
                    ?>
                    <div id="request-<?= $key ?>" class="<?= $class ?>">
                        <?php
                        if ($value->isDelayedByDefault()) {
                            echo $form->field($value, "[{$key}]quantity", [
                                'options' => ['class' => 'inline_block right_space']
                            ])->textInput(['class' => '', 'style' => 'width: 70px'])->error(false);
                            echo $form->field($value, "[{$key}]quantity_type", [
                                'options' => ['class' => 'inline_block left_space']
                            ])->dropDownList(RequestTemplateHelper::getQuantityTypesLabels(),
                                ['class' => ''])->label(false);
                        }
                        ?>
                        <?= $form->field($value, "[{$key}]template")->label(false)->textarea([
                            'cols' => 100,
                            'rows' => 5
                        ]) ?>
                    </div>
                </div>
            </div>
            <br>
        <?php endforeach ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

    </div>

    <div class="col-md-4 col-md-offset-1">
        <h4>Сокращения</h4>
        <div style="overflow-x: auto">
            <table class="table table-bordered">
                <tbody>
                <?php
                foreach (RequestTemplateHelper::getLabels() as $key => $value) {
                    echo "<tr><td>%{$key}%</td><td>{$value}</td></tr>";
                }
                ?>
                </tbody>
            </table>
            * - находится в разработке
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
