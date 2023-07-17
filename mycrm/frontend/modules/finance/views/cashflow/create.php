<?php

use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;


/* @var $this yii\web\View */
/* @var $model CompanyCashflow */
/* @var $type integer */

$title = 'Create Cashflow Income';

if ($type === CompanyCostItem::TYPE_INCOME) {
    $title = 'Create Cashflow Income';
}
if ($type === CompanyCostItem::TYPE_EXPENSE) {
    $title = 'Create Cashflow Expense';
}

$this->title = Yii::t('app', $title);
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Company Cashflows'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="company-cashflow-create">

    <?= $this->render('_form', [
        'model' => $model,
        'type' => $type,
    ]); ?>

</div>
