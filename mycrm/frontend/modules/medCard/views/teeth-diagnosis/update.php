<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardToothDiagnosis */

$this->title = Yii::t('app', 'Updating {something}', ['something' => $model->name]);
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-medkit"></i>&nbsp;{link}</li>',
    'label' => Yii::t('app', 'Med Card Teeth Diagnoses'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name];

?>
<div class="med-card-teeth-diagnosis-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
