<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\search\StaffReviewSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="staff-review-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-3">
            <?php echo $form->field($model, 'staff_id')
                ->dropDownList(\core\models\Staff::getOwnCompanyStaffList(),
                    ['prompt' => Yii::t('app', 'Select staff')]) ?>
        </div>
        <div class="col-sm-3">
            <?php echo $form->field($model, 'value') ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
