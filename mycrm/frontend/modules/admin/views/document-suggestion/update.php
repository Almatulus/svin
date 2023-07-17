<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentSuggestion */

$this->title = Yii::t('app', 'Update Document Suggestion: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Document Suggestions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="document-suggestion-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
