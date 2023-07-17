<?php

use core\models\City;
use core\models\company\Company;
use core\models\division\Division;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\division\search\DivisionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="division-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'company_id')->widget(Select2::className(), [
                'data' => ArrayHelper::map(Company::find()->orderBy('name')->all(), 'id', 'name'),
                'pluginOptions' => ['allowClear' => true],
                'options' => ['prompt' => ''],
            ]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'name')->widget(Select2::className(), [
                    'data' => ArrayHelper::map(Division::find()->orderBy('name')->all(), 'name', 'name'),
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['prompt' => ''],
            ]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'category_id')->widget(Select2::className(), [
                'data' => \core\models\Service::getCategoryServices(),
                'pluginOptions' => ['allowClear' => true],
                'options' => ['prompt' => ''],
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'city_id')->widget(Select2::className(), [
                'data' => ArrayHelper::map(City::find()->orderBy('name')->all(), 'id', 'name'),
                'pluginOptions' => ['allowClear' => true],
                'options' => ['prompt' => ''],
            ]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'address') ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'status')->dropDownList(\core\helpers\division\DivisionHelper::getStatuses(),['prompt' => '']) ?>
        </div>
    </div>


    <div class="form-group pull-right">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Reset'), ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
