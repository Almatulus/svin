<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentSuggestion */

$this->title = Yii::t('app', 'Create Document Suggestion');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Document Suggestions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-suggestion-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
