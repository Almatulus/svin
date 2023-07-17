<?php

use core\models\division\DivisionService;

/* @var $this yii\web\View */
/* @var $model DivisionService */
/* @var $insuranceCompanies \core\models\InsuranceCompany[] */

$this->title = Yii::t('app', 'Services');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_services"></div>{link}</li>', 'label' => $this->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>
<div class="division-service-create">

    <?= $this->render('_form', [
        'model' => $model,
        'products' => $products,
        'insuranceCompanies' => $insuranceCompanies
    ]) ?>

</div>
