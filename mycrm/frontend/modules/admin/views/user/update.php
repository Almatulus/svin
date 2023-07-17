<?php

/* @var $this yii\web\View */
/* @var $model core\models\user\User */

$this->title = Yii::t('app', 'Updating {something}', [
    'something' => $model->username,
]);

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Users'), 'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->user->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="user-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
