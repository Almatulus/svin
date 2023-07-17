<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \core\models\order\OrderDocumentTemplate */

$this->title = Yii::t('app', 'Updating {something}', ['something' => $model->name]);
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Company Documents'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name];

?>
<div class="company-document-update">

    <div class="company-document-form">

        <?php $form = ActiveForm::begin([
            'options' => [
                'class'   => 'simple_form',
                'enctype' => 'multipart/form-data'
            ]
        ]); ?>

        <ol>
            <?= $form->errorSummary($model); ?>

            <li class="control-group string company-document_name">
                <div class="controls">
                    <?= $form->field($model, 'name')
                             ->textInput(['maxlength' => true]) ?>
                </div>
            </li>
            <li class="control-group string company-document_name">
                <div class="controls">
                    <?= $form->field($model, 'filename')
                             ->textInput(['maxlength' => true]) ?>
                </div>
            </li>
        </ol>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Update'),
                ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>


</div>
