<?php


/* @var $this yii\web\View */
/* @var $model core\models\company\Tariff */

$this->title = Yii::t('app', 'Create Tariff');
$this->params['breadcrumbs'][] = [
    'template' => '<li>{link}</li>',
    'label'    => Yii::t('app', 'Tariffs'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tariffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tariff-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
