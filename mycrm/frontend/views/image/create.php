<?php

use kartik\file\FileInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\Image */

$this->title = Yii::t('app', 'Upload');
$this->params['breadcrumbs'][] = [
    'template' => '<li><span class="fa fa-image"></span> {link}</li>',
    'label' => Yii::t('app', 'Images'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="image-create">

    <div class="image-form">

        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <div class="form-group">
            <?= FileInput::widget([
                'name' => 'imageFile',
                'language' => 'ru',
            ]); ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Upload'), ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>


</div>
