<?php

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyContractor */

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Contractors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-contractor-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
