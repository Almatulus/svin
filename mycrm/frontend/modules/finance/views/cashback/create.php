<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model core\models\company\Cashback */

$this->title = Yii::t('app', 'Create Cashback');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cashbacks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cashback-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
