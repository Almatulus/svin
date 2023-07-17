<?php


/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerCategory */

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Customer Source'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-source-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
