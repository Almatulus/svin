<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model $model core\models\finance\CompanyCash */

$this->title = Yii::t('app', 'Update Cash') . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['template' => '<li><i class="fa fa-credit-card"></i> {link}</li>', 'label' => Yii::t('app','Cashes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name];
?>
<div class="company-cash-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
