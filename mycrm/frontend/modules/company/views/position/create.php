<?php

/* @var $this yii\web\View */
/* @var $model core\models\company\CompanyPosition */
/* @var $documentFormList array */

$this->title = Yii::t('app', 'Creating Company Position');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Company positions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-position-create">

    <?= $this->render('_form', [
        'model' => $model,
        'documentFormList' => $documentFormList,
    ]) ?>

</div>
