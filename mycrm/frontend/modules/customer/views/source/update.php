<?php

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerCategory */

$this->title = $model->name;

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Customer Source'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="customer-source-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
