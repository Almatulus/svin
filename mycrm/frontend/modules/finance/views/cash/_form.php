<?php

use core\models\division\Division;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model $model core\models\finance\CompanyCash */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-cash-form">

    <?php $form = ActiveForm::begin([
		'options' => ['class' => 'simple_form new_state_change'],
		'fieldConfig' => [
			'template' => '{input}<div class="col-sm-10 col-sm-offset-2">{error}</div>',
        ],
    ]); ?>

	<div class="row">
		<div class="col-sm-3">
			<div class="cash-register-box primary-box">
                <div class="box-title">Название</div>
                <?= $form->field($model, 'name')->textInput()->label(false)->error(false) ?>
				<div class="box-title">Начальный баланс (〒)</div>
				<?= $form->field($model, 'init_money')->textInput()->label(false) ?>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-3">
            <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList()) ?>
			<div class="cash-register-buttons-box">
				<?= $form->field($model, 'comments', ['options' => ['class' => '']])->textarea(['placeholder' => 'Комментарии', 'rows' => 20]) ?>
			</div>
			<div class="box-buttons">
                <button class="btn btn-primary" name="button" type="submit">Сохранить</button>
                <div class="pull-right">
                    <?= Html::a("отмена", Yii::$app->request->referrer) ?>
                </div>
            <div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
