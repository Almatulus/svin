<?php

use core\helpers\CompanyHelper;
use core\models\ServiceCategory;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\search\CompanySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-search">

    <div class="row">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>

        <div class="col-sm-3">
            <?= $form->field($model, 'name') ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($model, 'status')->dropDownList(CompanyHelper::getStatuses(), ['prompt' => '']) ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($model, 'publish')->dropDownList(CompanyHelper::getPublishStatuses(), ['prompt' => '']) ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($model, 'category_id')->dropDownList(ArrayHelper::map(ServiceCategory::getRootCategories(),
                'id', 'name'), ['prompt' => '']) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
        <?= Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success pull-right']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
