<?php

/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardToothDiagnosis */

$this->title                   = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-medkit"></i>&nbsp;{link}</li>',
    'label'    => Yii::t('app', 'Med Card Teeth Diagnoses'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="med-card-teeth-diagnosis-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
