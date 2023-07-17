<?php

use core\helpers\order\OrderConstants;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\order\search\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'status')
                ->dropDownList(OrderConstants::getStatuses(),
                    ['prompt' => Yii::t('app', 'Select status')])?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'service_id')->dropDownList(
                \core\models\Service::getCategoryServices(Yii::$app->user->identity->company->category),
                ['prompt' => Yii::t('app', 'Select service')]
            ) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'staff_id')->dropDownList(
                ArrayHelper::map(\core\models\Staff::find()->company()->timetableVisible()->all(), 'id', 'name'),
                ['prompt' => Yii::t('app', 'Select staff')]
            ) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'type')
                ->dropDownList(OrderConstants::getTypes(),
                    ['prompt' => Yii::t('app', 'Select order type')]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group pull-right">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
