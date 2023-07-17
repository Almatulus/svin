<?php

use core\helpers\StaffHelper;
use core\models\company\CompanyPosition;
use core\models\division\Division;
use core\models\ServiceCategory;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\search\StaffSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="staff-search">

    <?php $form = ActiveForm::begin([
        'action' => ['list'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, 'status')
                ->dropDownList(StaffHelper::getStatuses(), ['prompt' => Yii::t('app', 'Select status')]) ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'onlyActive')->checkbox() ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'usesMobileApp')->dropDownList([
                0 => 'Не использует',
                1 => 'Использует'
            ], ['prompt' => Yii::t('app', 'Select')]) ?>
        </div>
        <div class="col-sm-2">
            <?php
            $divisionQuery = Division::find();
            if ($model->onlyActive) {
                $divisionQuery->active(false);
            }
            ?>
            <?= $form->field($model, 'division_id')
                ->dropDownList(ArrayHelper::map($divisionQuery->select([
                    '{{%divisions}}.id',
                    '{{%divisions}}.name'
                ])->asArray()->all(), 'id', 'name'), ['prompt' => Yii::t('app', 'Select company division')]); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'company_position_id')
                ->dropDownList(ArrayHelper::map(CompanyPosition::find()->all(), 'id', 'name'),
                    ['prompt' => Yii::t('app', 'Select company position')]); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'service_category_id')
                ->dropDownList(
                    ArrayHelper::map(
                            ServiceCategory::find()->root()->all(),
                            'id',
                            'name'
                    ),
                    ['prompt' => Yii::t('app', 'Select service category')]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group pull-right">
                <?= Html::button(Yii::t('app', 'Send SMS'), ['class' => 'btn btn-default js-staff-send-sms']) ?>
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
