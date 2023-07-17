<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \core\models\order\OrderDocumentTemplate */

$this->title                   = Yii::t('app', 'Create Company Document');
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-envelope"></i>&nbsp;{link}</li>',
    'label'    => Yii::t('app', 'Company Documents'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-document-create">

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

            <li class="control-group file optional">
                <label class="file optional control-label" for="staff_avatar">Шаблон</label>
                <div class="controls">
                    <div class="btn fileinput-button js-image-field-wrapper">
                        <span class="icon sprite-add_photo_blue"></span>
                        <span>Добавить файл (doc, docx)</span>
                        <?= $form->field($model, 'path')
                                 ->fileInput(['class' => 'js-image-field']) ?>
                    </div>
                    <span class="chosen_photo hidden">
                        Выбранный файл (doc, docx):<span
                                class="photo_name"></span>&nbsp; &nbsp;
                        <a href="javascript:void(0)">Изменить</a>
                    </span>
                </div>
            </li>

        </ol>
        <div class="form-group">
            <?= Html::submitButton(
                Yii::t('app', 'Create'),
                ['class' => 'btn btn-success']
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>


</div>
