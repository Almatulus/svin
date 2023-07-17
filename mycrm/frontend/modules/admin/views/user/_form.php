<?php

use core\helpers\StaffHelper;
use core\helpers\user\UserHelper;
use core\models\company\Company;
use core\models\rbac\AuthItem;
use core\models\user\User;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username', [
        'options' => ['class' => 'reone-phone-input']
    ])->widget(MaskedInput::className(), [
        'mask' => '+7 999 999 99 99',
    ]) ?>

    <?= $form->field($model, 'password', [
        'inputOptions' => ['autocomplete' => 'new-password']
    ])->passwordInput() ?>

    <?= $form->field($model, 'role')
             ->dropDownList(
                 ArrayHelper::map(
                     AuthItem::getRoles(),
                     'name',
                     'name'
                 ),
                 ['prompt' => Yii::t('app', 'Select role')]
             ) ?>

    <?= $form->field($model, 'user_permissions')->widget(Select2::className(), [
        'data'    => StaffHelper::getModulePermissions(),
        'options' => ['multiple' => true],
        'size'    => 'sm'
    ]) ?>

    <?= $form->field($model, 'company_id')->widget(Select2::className(), [
        'data' => ArrayHelper::map(Company::find()->all(), 'id', 'name')
    ]); ?>

    <?= $form->field($model, 'status')
             ->dropDownList(UserHelper::getStatuses()) ?>

    <?= isset($model->user) ? $model->user->access_token : "" ?>

    <?= isset($model->user->staff) ? $model->user->staff->getFullName() : "" ?>

    <div class="form-group">
        <?= Html::submitButton(
            Yii::t('app', 'Save'),
            ['class' => 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
