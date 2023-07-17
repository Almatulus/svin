<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentFormGroup */

$this->title = Yii::t('app', 'Create Document Form Group');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Document Form Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-form-group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
