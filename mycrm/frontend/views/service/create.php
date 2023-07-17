<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model core\models\Service */

$this->title                      = Yii::t('app', 'Create Service');
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_services"></div>{link}</li>', 'label' => Yii::t('app', 'Services'), 'url' => ['index']];
$this->params['breadcrumbs'][]    = $this->title;
$this->params['mainContentClass'] = 'services';
?>
<div class="service-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
