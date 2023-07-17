<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentForm */

$this->title = Yii::t('app', 'Create Document Form');
$this->params['breadcrumbs'][] = [
    'template' => '<li><span class="fa fa-image"></span> {link}</li>',
    'label'    => Yii::t('app', 'Document Forms'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-form-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
