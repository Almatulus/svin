<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-category-form">

    <?php $form = ActiveForm::begin(['options' => ['class' => 'simple_form']]); ?>

    <ol>
        <li class="control-group string optional customer-category_name">
            <div class="controls">
                <?= $form->field($model, 'name')->textInput(['class' => 'string options']) ?>
            </div>
        </li>
        <li class="control-group string optional customer-category_discount">
            <div class="controls">
                <?= $form->field($model, 'discount', [
                    'template' => "{label}\n{input} %\n{hint}\n{error}"
                ])->textInput(['style' => 'width:44px;'])?>
            </div>
        </li>
        <li class="control-group string optional customer-category_cashback_percent">
            <div class="controls">
                <?= $form->field($model, 'cashback_percent', [
                    'template' => "{label}\n{input} %\n{hint}\n{error}"
                ])->textInput(['style' => 'width:44px;']) ?>
            </div>
        </li>
        <li class="control-group string optional customer-category_color">
            <div class="controls">
                <?= $form->field($model, 'color')
                    ->textInput(['maxlength' => true,
                                 'type' => 'color',
                                 'style' => 'width:44px; padding:1px 2px;']) ?>
            </div>
        </li>
    </ol>

    <?php if (Yii::$app->request->referrer == null) {
        $url = ['index'];
    } else {
        $url = Yii::$app->request->referrer;
    }
    ?>
    <div class="form-actions">
        <div class="pull_right cancel-link">
            <?= Html::a('Отмена', $url) ?>
        </div>
        <div class="with-max-width">
            <button class="btn btn-primary" data-disable-with="Processing..."
                    data-enable-with="<span class='icon sprite-add_customer_save'></span>Сохранить"
                    icon="sprite-add_customer_save" name="commit" type="submit">
                <span class="icon sprite-add_customer_save"></span>Сохранить
            </button>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
