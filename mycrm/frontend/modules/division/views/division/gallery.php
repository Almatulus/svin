<?php
/** @var View $this */
/** @var Division $model */

use core\models\division\Division;
use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title                   = Yii::t('app', 'Gallery');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Divisions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = "{$model->name} ({$model->address})";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="img-container">

    <div class="row">
        <?php foreach ($model->divisionImages as $divisionImage): ?>
            <div class="col-sm-2" style="margin: 15px 5px; padding: 5px;">
                <?php
                if ($divisionImage->image_id) {
                    echo Html::img( $divisionImage->image->getAvatarImageUrl());
                }
                ?>
                <br/>
                <?= Html::a(Yii::t('app', 'Remove'), ['image-remove', 'id' => $divisionImage->division->key, 'image' => $divisionImage->id]) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
                <?php
                    echo $form->field($model, 'imageFiles[]')->widget(FileInput::className(), [
                        'language' => 'ru',
                        'options' => ['multiple' => true],
                        'pluginOptions' => ['previewFileType' => 'image', 'uploadUrl' => Url::to(['gallery', 'id' => $model->key])],
                        'pluginEvents' => ['filebatchuploadcomplete' => "function(event, files, extra) {location.reload();}"]
                    ])->label(false);
                ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
