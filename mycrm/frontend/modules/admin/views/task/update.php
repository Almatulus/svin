<?php

/* @var $this yii\web\View */
/* @var $model core\models\company\Task */

$this->title = Yii::t('app', 'Update Task: {nameAttribute}', [
    'nameAttribute' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tasks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="task-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
