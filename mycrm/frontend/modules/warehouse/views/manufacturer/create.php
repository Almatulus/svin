<?php


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Manufacturer */

$this->title = Yii::t('app', 'Manufacturer');
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Manufacturer'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>

<?= $this->render('/common/_tabs') ?>

<div class="manufacturer-create">

    <?= $this->render('_form', [
        'model' => $model
    ]) ?>

</div>
