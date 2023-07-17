<?php

use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\Staff;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model DivisionService */
/* @var $form yii\widgets\ActiveForm */
/* @var $insuranceCompanies \core\models\InsuranceCompany[] */
?>

<div class="service-form">

    <?php $form
        = ActiveForm::begin(['options' => ['class' => 'simple_form new_service']]); ?>

    <ol>
        <li class="control-group string optional service_name">
            <div class="controls">
                <?= $form->field($model, 'service_name',
                    ['template' => "{label}\n{input}\n{hint}\n{error}"])
                    ->textInput(['class' => 'string options']) ?>
            </div>
        </li>
        <li class="control-group integer optional service_duration">
            <div class="controls">
                <?= $form->field($model, 'average_time',
                    ['template' => "{label}\n{input} минут\n{hint}\n{error}"])
                    ->textInput(['class' => 'numeric integer optional small-input']) ?>
            </div>
        </li>
        <li class="control-group boolean optional service_publish">
            <?= $form->field($model, 'publish', [
                'options' => ['style' => 'display: inline-block;'],
            ])->checkbox(['class' => 'boolean optional control-label']) ?>
        </li>
        <li class="control-group select optional service_category">
            <label class="integer optional control-label"
                   for="service_trade_id">Заведение</label>
            <div class="controls">
                <?= $form->field($model, 'division_ids')
                    ->widget(Select2::className(), [
                        'data'          => Division::getOwnCompanyDivisionsList(),
                        'options'       => ['multiple' => true],
                        'pluginOptions' => [
                            'allowClear' => false,
                            'width'      => '240px',
                        ],
                        'showToggleAll' => true,
                        'theme'         => Select2::THEME_DEFAULT
                    ])->label(false) ?>
            </div>
        </li>
        <li class="control-group select optional service_category">
            <label class="integer optional control-label"
                   for="service_trade_id">Категория</label>
            <div class="controls">
                <?= $form->field($model, 'category_ids')
                    ->widget(Select2::className(), [
                        'data'          => \core\models\ServiceCategory::getAll(),
                        'pluginOptions' => [
                            'allowClear' => false,
                            'width'      => '240px',
                        ],
                        'options'       => [
                            'multiple' => true,
                        ],
                        'showToggleAll' => false,
                        'theme'         => Select2::THEME_DEFAULT
                    ])->label(false) ?>
            </div>
        </li>
        <?php
        $checked = '';
        $style = "display:none";
        if ($model->price_max !== null) {
            $checked = 'checked';
            $style = "";
        }
        ?>
        <li class="control-group decimal optional service_price">
            <label class="decimal optional control-label" for="service_price">Цена</label>
            <div class="controls">
                <?= $form->field($model, 'price', [
                    'template' =>
                        "{input} <span class=\"currency-symbol\">〒</span> <span class=\"light-text\">(гросс)</span>\n{hint}\n{error}"
                ])
                    ->textInput([
                        'id'              => "service_price",
                        "data-lumo-price" => "true",
                        'class'           => 'small-input',
                        'size'            => 30
                    ]) ?>
                <div>
                    <input id="service_price_range"
                           type="checkbox" <?= $checked ?>>
                    <label class="string optional control-label"
                           for="service_price_range">Ценовой диапазон</label>
                </div>
            </div>
        </li>
        <li class="control-group decimal optional service_price_max"
            style="<?= $style ?>">
            <label class="decimal optional control-label"
                   for="service_price_max">Макс. цена</label>
            <div class="controls">
                <?php // ToDo add max price ?>
                <?= $form->field($model, 'price_max',
                    ['template' => "{input} <span class=\"currency-symbol\">〒</span> <span class=\"light-text\">(гросс)</span>\n{hint}\n{error}"])
                    ->textInput(['class' => 'small-input service-max-price-input', 'size' => 30]) ?>
            </div>
        </li>
        <li class="control-group string optional service_desciption">
            <div class="controls">
                <?= $form->field($model, 'description')->textarea([
                    'size'      => '50',
                    'class'     => 'string options',
                    'maxlength' => true
                ]) ?>
            </div>
        </li>
        <li class="control-group string optional service_staff">
            <div class="controls">
                <?= $form->field($model, 'staff')
                    ->widget(Select2::className(), [
                        'data'          => ArrayHelper::map(
                            Staff::find()
                                ->company(false)
                                ->permitted()
                                ->enabled()
                                ->timetableVisible()
                                ->all(),
                            'id',
                            'fullName'
                        ),
                        'options'       => ['multiple' => true],
                        'size'          => 'sm',
                        'pluginOptions' => ['width' => '240px'],
                        'theme'         => Select2::THEME_DEFAULT
                    ]) ?>
            </div>
        </li>
        <li class="control-group string optional service_products advanced"
            style="">
            <label class="string optional control-label"
                   for="service_products"><?= Yii::t('app',
                    'Formula') ?></label>
            <div class="controls">
                <?= $this->render('_dynamic_form', [
                    'form'     => $form,
                    'model'    => $model,
                    'products' => $products
                ]) ?>
            </div>
        </li>
        <li class="control-group boolean optional service_is_trial">
            <?= $form->field($model, 'is_trial', [
                'options' => ['style' => 'display: inline-block;'],
            ])->checkbox(['class' => 'boolean optional control-label']) ?>
        </li>
        <li class="control-group select optional">
            <div class="controls">
                <?= $form->field($model, 'insurance_company_id')->dropDownList(
                    \core\models\InsuranceCompany::map(),
                    ['prompt' => Yii::t('app', "Select insurance company")]
                );
                ?>
            </div>
        </li>
        <li class="control-group string optional service_insurance_companies advanced"
            style="">
            <label class="string optional control-label"
                   for="service_products"><?= Yii::t('app',
                    'Insurance companies prices') ?></label>
            <div class="controls">
                <?= $this->render('_dynamic_form_insurance', [
                    'form'     => $form,
                    'model'    => $model,
                    'insuranceCompanies' => $insuranceCompanies
                ]) ?>
            </div>
        </li>
        <li class="control-group string optional">
            <div class="controls">
                <?= $form->field($model, 'code_1c') ?>
            </div>
        </li>
        <li class="control-group select optional">
            <div class="controls">
                <?= $form->field($model, 'notification_delay')->dropDownList(
                    \core\helpers\division\ServiceHelper::all(),
                    ['prompt' => Yii::t('app', "Select")]
                );
                ?>
            </div>
        </li>
    </ol>
    <div class="form-actions">
        <div class="with-max-width">
            <div class="pull_right cancel-link">
                <?= Html::a('Отмена', Yii::$app->request->referrer) ?>
            </div>
            <button class="btn btn-primary" type="submit">
                <span class="icon sprite-add_customer_save"></span>Сохранить
            </button>
            <button class="btn btn-default" type="submit" name="action"
                    value="add-another">
                <?= Yii::t('app', 'Save and add another') ?>
            </button>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
var max_price = null;
$("#service_price_range").change(function() {
		if(this.checked) {
            $('.service-max-price-input').val(max_price);
			$('.service_price_max').show();
		} else {
		    var max_price_element = $('.service-max-price-input');
		    max_price = max_price_element.val();
		    max_price_element.val('');
			$('.service_price_max').hide();
		}
	});
JS;
$this->registerJs($js);
?>
