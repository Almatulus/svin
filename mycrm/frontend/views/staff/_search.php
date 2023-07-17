<?php

use core\models\company\CompanyPosition;
use core\models\division\Division;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\search\StaffSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="staff-search">

    <?php $form = ActiveForm::begin([
        'action'      => ['index'],
        'fieldConfig' => [
            'template' => "{input}\n{hint}\n{error}"
        ],
        'method'      => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'term')->textInput(['placeholder' => Yii::t('app', 'Search staff')]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'division_id')
                ->dropDownList(Division::getOwnCompanyDivisionsList(),
                    ['prompt' => Yii::t('app', 'All Divisions')]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'company_position_id')
                ->dropDownList(ArrayHelper::map(CompanyPosition::getOwnCompanyPositions(), 'id', 'name'),
                    ['prompt' => Yii::t('app', 'All company positions')]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                <?= Html::a(
                    '<span class="icon sprite-add_employed"></span>Добавить сотрудника',
                    ['create'],
                    ['class' => 'btn btn-primary pull-right left_space']
                ); ?>
                <?= Html::a(
                    Yii::t('app', 'Archive'),
                    ['archive'],
                    ['class' => 'btn btn-default pull-right right_space']
                ); ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
