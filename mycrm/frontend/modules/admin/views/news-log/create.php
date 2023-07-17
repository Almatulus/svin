<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model core\models\NewsLog */

$this->title = Yii::t('app', 'Create News Log');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'News Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="news-log-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
