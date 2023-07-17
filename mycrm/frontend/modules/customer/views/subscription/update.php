<?php

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerSubscription */

$this->title = $model->key;
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 
    'label' => Yii::t('app', 'Customers'), 
    'url' => ['/customer/customer/index']
];
$this->params['breadcrumbs'][] = [ 
    'label' => Yii::t('app', 'Season tickets'), 
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-subscription-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
