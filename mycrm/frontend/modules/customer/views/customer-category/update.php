<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerCategory */

$this->title = Yii::t('app','Update Customer Category') . ': ' . $model->name;

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Customer Categories'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app','Update');
?>
<div class="customer-category-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
