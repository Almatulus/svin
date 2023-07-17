<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model core\models\ServiceCategory */

$this->title                      = Yii::t('app', 'Create Service Category');
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_services"></div>{link}</li>', 'label' => Yii::t('app', 'Service Category'), 'url' => ['index']];
$this->params['breadcrumbs'][]    = $this->title;
$this->params['mainContentClass'] = 'services';
?>
<div class="service-category-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
