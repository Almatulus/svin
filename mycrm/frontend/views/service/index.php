<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $category core\models\ServiceCategory */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Services');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_services"></div>{link}</li>', 'label' => $this->title, 'url' => ['index']];
?>

<div class="service-index">

    <div class="column_row row buttons-row">
        <div class="col-sm-12 right-buttons">
            <?= Html::a(Yii::t('app', 'Create service'), ['create', 'key' => $category->id], ['class' => 'btn btn_blue']) ?>
        </div>
    </div>

    <?= GridView::widget([
        'showOnEmpty' => false,
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'column_row data_table'],
        'tableOptions' => ['class' => 'table table-bordered'],
        'emptyTextOptions' => ['style' => 'margin-top: 60px'],
        'emptyText' => '<div class="col-md-2"></div>
			<div class="col-md-8">
			<div class="empty-list-icon"><i class="icon sprite-first_resource"></i></div>
			<h2 class="empty-list-heading-centered">Услуг не найдено</h2>
			</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',

            [
                'class' => \yii\grid\ActionColumn::className(),
                'template'=>'{update} {delete}',
                'buttons'=>[
                    'update'=>function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-pencil"></span>',
                            ["update", "id" => $model->id],
                            [
                                'title' => Yii::t('app', 'Update'),
                                'class' => 'btn btn-default btn-sm'
                            ]
                        );
                    },
                    'delete'=>function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ["delete", "id" => $model->id],
                            [
                                'title' => Yii::t('app', 'Delete'),
                                'class' => 'btn btn-default btn-sm',
                                'data' => [
                                    'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>
</div>
