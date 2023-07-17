<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\admin\search\UserLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'User Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-log-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'username',
                'format'    => 'html',
                'filter'    => \yii\widgets\MaskedInput::widget([
                    'model'     => $searchModel,
                    'attribute' => 'username',
                    'mask'      => '+7 999 999 99 99',
                ]),
                'value'     => 'user.username',
            ],
            [
                'attribute' => 'ip_address',
                'filter'    => false
            ],
            [
                'attribute' => 'user_agent',
                'filter'    => false
            ],
            [
                'attribute' => 'datetime',
                'format'    => 'datetime'
            ],
            [
                'attribute' => 'action',
                'filter'    => \core\helpers\user\UserLogHelper::all(),
                'value'     => 'actionLabel'
            ],
        ],
    ]); ?>
</div>
