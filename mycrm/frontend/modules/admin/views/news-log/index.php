<?php

use core\models\NewsLog;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\admin\search\NewsLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'News Logs');
$this->params['breadcrumbs'][] = $this->title;
$this->params['bodyID'] = 'summary';
?>
<div class="news-log-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create News Log'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'text:ntext',
            'link:url',
            [
                'attribute' => 'status',
                'value' => function(NewsLog $model) {
                    return \core\helpers\NewsLogHelper::getStatuses()[$model->status];
                },
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
