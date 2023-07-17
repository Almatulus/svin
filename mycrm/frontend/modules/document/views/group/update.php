<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentFormGroup */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
        'modelClass' => 'Document Form Group',
    ]) . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Document Form Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="document-form-group-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
