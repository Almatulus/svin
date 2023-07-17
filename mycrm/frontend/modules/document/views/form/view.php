<?php

use core\models\document\DocumentFormElement;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentForm */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Document Forms'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-form-view">

    <h3><?= Html::encode($this->title) ?></h3>

    <p>
        <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Import'), '#', [
            'data-url' => Url::to(['import', 'id' => $model->id]),
            'class'    => 'btn btn-default js-import'
        ]) ?>
        <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Upload'), '#', [
            'data-url' => Url::to(['upload', 'id' => $model->id]),
            'class'    => 'btn btn-default js-import'
        ]) ?>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Elements'), ['elements', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data'  => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method'  => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Duplicate'), ['duplicate', 'id' => $model->id], ['class' => 'btn btn-danger pull-right']) ?>
    </p>

    <?= DetailView::widget([
        'model'      => $model,
        'attributes' => [
            'id',
            'name',
            'has_dental_card:boolean',
            'doc_path'
        ],
    ]) ?>

    <div class="row">
        <div class="col-sm-12">
            <h4>Elements</h4>
            <?= GridView::widget([
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $model->getElements()
                            ->orderBy('order ASC')
                            ->all(),
                    ]),
                    'columns' => [
                        'id',
                        'label',
                        'key',
                        'order',
                        'raw_id',
                        [
                            'attribute' => 'group.label',
                            'label'     => \Yii::t('app', 'Group'),
                        ],
                        [
                            'attribute' => 'type',
                            'value'     => function (DocumentFormElement $model) {
                                return $model->getTypeName();
                            },
                        ],
                        'search_url',
                        'depends_on',
                    ],
                ]); ?>
        </div>
    </div>

</div>
