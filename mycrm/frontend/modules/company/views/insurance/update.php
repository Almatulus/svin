<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\company\Insurance */

$this->title = Yii::t('app', 'Updating {something}', ['something' => $model->name]);
$this->params['breadcrumbs'][] = [
        'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
        'label' => Yii::t('app', 'Insurances'),
        'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name];


?>
<div class="insurance-update">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton(
            Yii::t('app', 'Update'),
            ['class' => 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
