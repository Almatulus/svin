<?php

use core\helpers\division\DivisionHelper;
use core\models\division\Division;
use frontend\modules\division\search\DivisionSearch;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel DivisionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Divisions');
$this->params['breadcrumbs'][] = ['template' => '<li><i class="fa fa-scissors"></i> {link}</li>', 'label' => $this->title];
?>
<div class="division-index">

    <div class="actions">
        <?= Html::a(Yii::t('app', 'Add'), ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'hover' => 'true',
        'responsiveWrap' => false,
        'columns' => [
            [
                'attribute' => 'status',
                'filter' => DivisionHelper::getStatuses(),
                'format' => 'html',
                'value' => function($model)
                {
                    switch($model->status){
                        case Division::STATUS_ENABLED:
                            return Html::tag("span", Yii::t('app', Division::STATUS_ENABLED_NAME),
                                ["class" => "label label-success"]);
                            break;
                        case Division::STATUS_DISABLED:
                            return Html::tag("span", Yii::t('app', Division::STATUS_DISABLED_NAME),
                                ["class" => "label label-danger"]);
                            break;
                    }
                    return "";
                }
            ],
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function ($data)
                {
                    return Html::a($data->name, ["update", "id" => $data->key]);
                }
            ],
            'address',
            [
                'attribute' => 'city_id',
                'value' => function($model)
                {
                    return $model->city->name;
                }
            ],
            [
                'class' => \yii\grid\ActionColumn::className(),
                'template'=>'{gallery} {delete}',
                'buttons'=>[
                    'gallery'=>function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-camera"></span>',
                            ["gallery", "id" => $model->key],
                            [
                                'title' => Yii::t('app', 'Settings'),
                                'class' => 'btn btn-default btn-sm'
                            ]
                        );
                    },
                    'delete'=>function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ["delete", "id" => $model->key],
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
