<?php

use core\helpers\color\ColorSelect2;
use core\helpers\customer\CompanyCustomerHelper;
use core\helpers\GenderHelper;
use core\models\customer\CustomerCategory;
use core\models\division\DivisionService;
use core\models\Staff;
use frontend\search\CustomerSearch;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model frontend\search\CustomerSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="column_row customer-search" style="padding-left:15px">

    <h4 style="padding-left: 0">Фильтры</h4>

    <?php //$form->field($model, 'sMode')->dropDownList(CustomerSearch::getSearchModeMap()); ?>

    <?= $form->field($model, 'sCategories')->widget(ColorSelect2::className(), [
        'data' => CustomerCategory::getCategoryMapSelect2(),
        'options' => ['multiple' => true, 'placeholder' => Yii::t('app','Select category')],
        'pluginOptions' => [
            'allowClear' => true,
            'templateSelection' => new JsExpression('formatRepoSelection'),
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        ],
        'showToggleAll' => false,
        'maintainOrder' => true,
        'size' => 'sm'
    ]); ?>

    <?= $form->field($model, 'sGender')->checkboxList(GenderHelper::getGenders()); ?>

    <?= $form->field($model, 'sStaff')->widget(Select2::className(), [
        'data' => Staff::getOwnCompanyStaffList(),
        'options' => ['multiple' => true, 'placeholder' => Yii::t('app','-- Select staff --')],
        'pluginOptions' => [
            'allowClear' => true,
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
        'showToggleAll' => false,
        'size' => 'sm'
    ]); ?>

    <?php // TODO bug with Select2 Ajax after page refresh. Option titles disappear, IDs written instead of them ?>
    <?php // TODO Addition: not working with other Select2 ?>
    <?= $form->field($model, 'sService')->widget(Select2::className(), [
        'data' => DivisionService::getOwnCompanyDivisionServicesList(),
        'options' => ['multiple' => true, 'placeholder' => Yii::t('app','-- Select service --')],
        'pluginOptions' => [
            'allowClear' => true,
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
        'showToggleAll' => false,
        'size' => 'sm'
    ]); ?>

    <label class="control-label"><?=Yii::t('app','Birth Date')?></label>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'sBirthFrom',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'sBirthTo',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','To date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'mm-dd'
                ]
            ]) ?>
        </div>
    </div>

    <label class="control-label"><?=Yii::t('app','Money spent')?></label>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'sPaidMin',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From').'</span>{input}</div>',
            ])->textInput() ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'sPaidMax',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','To').'</span>{input}</div>',
            ])->textInput() ?>
        </div>
    </div>

    <label class="control-label"><?=Yii::t('app','Visit count')?></label>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'sVisitCountMin',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From').'</span>{input}</div>',
            ])->textInput() ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'sVisitCountMax',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','To').'</span>{input}</div>',
            ])->textInput() ?>
        </div>
    </div>

    <label class="control-label"><?=Yii::t('app','Search has Visits')?></label>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'sVisitedFrom',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'sVisitedTo',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','To date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
    </div>

    <br>

    <label class="control-label"><?=Yii::t('app','First visit')?></label>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'sFirstVisitedFrom',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'sFirstVisitedTo',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','To date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
    </div>
    <br>

    <?= $form->field($model, 'sSMSMode')->label(false)->dropDownList(CustomerSearch::getSMSMap()); ?>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'sSMSFrom',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','From date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'sSMSTo',[
                'template' => '<div class="input-group"><span class="input-group-addon">'.Yii::t('app','To date').'</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <label class="control-label"><?=Yii::t('app','Iin')?></label>
            <?= $form->field($model, 'sIin')->textInput()->label(false) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <label class="control-label"><?=Yii::t('app','Card Number')?></label>
            <?= $form->field($model, 'sCardNumber')->textInput()->label(false) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <label class="control-label"><?= Yii::t('app', 'Division') ?></label>
            <?= $form
                ->field($model, 'sDivision')
                ->label(false)
                ->dropDownList(\core\models\division\Division::getOwnCompanyDivisionsList(), [
                    'prompt' => Yii::t('app', 'Select division')
                ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <label class="control-label"><?= Yii::t('app', 'City') ?></label>
            <?= $form
                ->field($model, 'sCity')
                ->label(false)
                ->dropDownList(CompanyCustomerHelper::getCities(Yii::$app->user->identity->company_id), [
                    'prompt' => Yii::t('app', 'Select city')
                ]); ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'js-customers-search btn btn-primary']); ?>
        <?= Html::a(Yii::t('app', 'Reset'), ['index'], ['class' => 'btn btn-default']) ?>
    </div>

</div>
