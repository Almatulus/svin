<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardCommentCategory */
/* @var $categories array */

$this->title = Yii::t('app', 'Comment Template Category');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => $model->name
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>

<div class="comment-template-category-update">

    <?= $this->render('_form', compact('model', 'categories', 'serviceCategories')) ?>

</div>
