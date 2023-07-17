<?php


/* @var $this yii\web\View */
/* @var $model core\models\division\Division */
/* @var $divisionPhones core\models\division\DivisionPhone[] */

$this->title                   = Yii::t('app', 'New division');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::$app->user->identity->company->name,
    'url' => ['/company/default/update', 'id' => Yii::$app->user->identity->company_id]
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="division-create">
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
