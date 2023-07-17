<?php


/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerSubscription */

$this->title = Yii::t('app', 'Create');

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

<div class="customer-subscription-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
