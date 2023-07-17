<?php


/* @var $this yii\web\View */
/* @var $model core\models\FaqItem */

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = [
    'template' => '<li><span class="fa fa-question-circle"></span> {link}</li>',
    'label' => Yii::t('app', 'FAQ'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="faq-item-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
