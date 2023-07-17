<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$backUrl = Yii::$app->request->referrer;
if (!$backUrl) {
    $backUrl = ['index'];
}

?>

<div class='manufacturer-form'>
    <?php $form = ActiveForm::begin(['id' => 'manufacturer-form', 'options' => ['class' => 'simple_form']]); ?>

    <div class="column_row">
        <fieldset>
            <ol>
                <li class="control-group string required manufacturer_name">
                    <div class='controls'>
                        <?= $form->field($model, 'name', [
                            'options' => ['class' => ''],
                            'template' => "{label}\n{input}\n{error}",
                            'errorOptions' => ['class' => 'help-block', 'style' => 'margin:0']
                        ]); ?>
                    </div>
                </li>
            </ol>
        </fieldset>
    </div>

    <div class="form-actions">
        <div class="pull_right cancel-link">
            <?= Html::a('Отмена', $backUrl) ?>
        </div>
        <div class="with-max-width">
            <button class="btn btn-primary" type="submit">
                <span class="icon sprite-add_customer_save"></span>Сохранить
            </button>
        </div>
    </div>
    <?php $form->end(); ?>
</div>