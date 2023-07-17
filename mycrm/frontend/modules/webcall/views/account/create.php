<?php
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $model core\forms\webcall\AccountForm */

$this->title = Yii::t('app', 'Create account');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Web Calls'),
    'url'      => ['default/settings']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="webcall-account-create">
    <div class="webcall-account-form">
        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'options'        => ['tag' => 'li', 'class' => 'control-group'],
                'template'       => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
                'wrapperOptions' => ['class' => 'controls'],
            ],
            'options'     => ['class' => 'simple_form']
        ]); ?>
        <ol>
            <?= $form->field($model, 'division_id')->widget(\kartik\select2\Select2::class, [
                'data'          => \core\models\division\Division::getOwnCompanyDivisionsList(),
                'size'          => 'sm',
                'pluginOptions' => [
                    'width'      => '240px',
                    'allowClear' => true
                ]
            ]); ?>
            <?= $form->field($model, 'name') ?>
            <?= $form->field($model, 'email') ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'password_confirm')->passwordInput() ?>
        </ol>
        <div class="box-buttons">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Cancel'), ['default/settings'], ['style' => 'margin-left: 60px']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
