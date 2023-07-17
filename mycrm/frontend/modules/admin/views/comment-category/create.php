<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardCommentCategory */
/* @var $categories array */

$this->title = Yii::t('app', 'Comment Template Category');
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>
<div class="comment-template-category-create">

    <?= $this->render('_form', compact('model', 'categories', 'serviceCategories')) ?>

</div>
