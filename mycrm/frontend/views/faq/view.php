<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model core\models\FaqItem */

$this->title = Yii::t('app', 'FAQ') . " #" . $model->id;
$this->params['breadcrumbs'][] = [
    'template' => '<li><span class="fa fa-question-circle"></span> {link}</li>',
    'label' => Yii::t('app', 'FAQ'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="faq-item-view">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'question',
            'answer:ntext',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
