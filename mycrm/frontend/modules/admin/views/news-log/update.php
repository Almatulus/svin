<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\NewsLog */

$this->title = Yii::t('app', 'Update News Log');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $model->id
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="news-log-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
