<?php

use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\Service */
/* @var $form yii\widgets\ActiveForm */

?>
<style>
	.select2-selection {
		box-shadow: inset 0 1px 2px #dedede;
		border-radius: 3px;
		background: #fff;
		border: 1px solid #bfbfbf;
		height: 28px;
		vertical-align: middle;
		font-size: 13px;
		color: #313140;
		/* padding-top: 0; */
		padding-bottom: 0;
	}

	.select2-container--default .select2-results__option[aria-selected=true] {
		background-color: inherit;
	}

	.select2-container--default .select2-results__option--highlighted[aria-selected] {
		background: #e3fffb;
		color: #313140;
	}

</style>

<div class="service-form">

	<?php $form = ActiveForm::begin(['options' => ['class' => 'simple_form new_service']]); ?>

	<ol>
		<li class="control-group string optional service_name">
			<div class="controls">
				<?= $form->field($model, 'name', ['template' => "{label}\n{input}\n{hint}\n{error}"])->textInput(['class' => 'string options']) ?>
			</div>
		</li>
		<li class="control-group integer optional service_duration">
			<label class="integer optional control-label" for="service_duration">Duration</label>
			<div class="controls">
				<?php // ToDo add duration ?>
				<!--<? //= $form->field($model, 'duration')->textInput(['class' => 'numeric integer optional small-input'])->label(false) ?>-->
				<input class="numeric integer optional small-input" id="service_duration" min="1"
					   name="Service[duration]" type="text"> minutes
			</div>
		</li>
		<li class="control-group select optional service_category">
			<label class="integer optional control-label" for="service_trade_id">Категория</label>
			<?php // ToDo change ?>
			<div class="controls">
				<?= $form->field($model, 'category_id')->widget(Select2::className(), [
					'data' => ArrayHelper::map(\core\models\ServiceCategory::find()->where("parent_category_id IS NOT NULL")->all(), 'id', 'name'),
					'pluginOptions' => [
						'allowClear' => false,
						'width' => '240px',
					],
					'showToggleAll' => false,
					'theme' => Select2::THEME_DEFAULT
				])->label(false) ?>
			</div>
		</li>
		<li class="control-group decimal optional service_price">
			<label class="decimal optional control-label" for="service_price">Цена</label>
			<div class="controls">
				<?php // ToDo add price ?>
				<p>
					<?php //$form->field($model, 'price')->textInput(['class' => 'small-input', 'size' => 30]) ?>
					<input class="small-input" data-lumo-price="true" id="service_price" name="service[price]" size="30"
						   type="text">
					<span class="currency-symbol">〒</span><span class="light-text">(гросс)</span>
				</p>
				<div>
					<input id="service_price_range" type="checkbox" value="0">
					<label class="string optional control-label" for="service_price_range">Ценовой диапазон</label>
				</div>
			</div>
		</li>
		<li class="control-group decimal optional service_price_max" style="display: none;">
			<label class="decimal optional control-label" for="service_price_max">Макс. цена</label>
			<div class="controls">
				<?php // ToDo add max price ?>
				<input class="small-input" id="service_price_max" name="service[price_max]" size="30" type="text">
				<span class="currency-symbol">〒</span><span class="light-text">(gross)</span>
			</div>
		</li>
		<!-- <li class="control-group string optional service_desciption">
		<div class="controls">
				<?php // ToDo add descr ?>
				<?php // $form->field($model, 'description')->textArea(['size' => '50', 'class' => 'string options', 'maxlength' => true]) ?>
			</div>
		</li> -->
		<!-- <li class="control-group boolean optional service_special_service">
            <?php /** $form->field($model, 'has_promotion', [
		 * 'options' => ['style' => 'display: inline-block;'],
		 * ])->checkbox(['class' => 'boolean optional control-label']) */ ?>
        </li>-->
		<!-- <li class="control-group select optional staff_online_booking">
			<div class="controls">
                <?php // $form->field($model, 'online_booking')->dropDownList()?>
			</div>
		</li>-->
	</ol>
	<div class="form-actions">
		<div class="pull_right cancel-link">
			<?= Html::a('Отмена', Yii::$app->request->referrer) ?>
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

<?php
$js = <<<JS
$(":checkbox").change(function() {
		if(this.checked) {
			$('.service_price_max').show();
		} else {
			$('service_price_max').hide();
		}
	});
JS;
$this->registerJs($js);
?>
