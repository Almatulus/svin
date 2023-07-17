<?php

use core\models\company\Company;
use core\models\company\Task;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\admin\search\TaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Tasks') . ($searchModel->company ? " {$searchModel->company->name}" : '');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create', 'company_id' => $searchModel->company_id],
            ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'rowOptions'   => function (Task $model) {
            return $model->isCompleted() ? ['class' => 'success'] : [];
        },
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'type',
                'filter'    => Task::getTypes(),
                'value'     => 'typeName'
            ],
            'comments:ntext',
            [
                'attribute' => 'start_date',
                'filter'    => false,
                'format'    => 'datetime',
            ],
            [
                'attribute' => 'due_date',
                'filter'    => false,
                'format'    => 'datetime',
            ],
            [
                'attribute' => 'end_date',
                'filter'    => false,
                'format'    => 'datetime',
            ],
            [
                'attribute' => 'company_id',
                'filter'    => Select2::widget([
                    'model'     => $searchModel,
                    'attribute' => 'company_id',
                    'data'      => Company::map(),
                    'options'   => [
                        'placeholder' => Yii::t('app', 'Select'),
                    ]
                ]),
                'value'     => 'company.name'
            ],
            [
                'class'          => 'yii\grid\ActionColumn',
                'buttons'        => [
                    'complete' => function ($url, Task $model) {
                        return Html::a('<i class="glyphicon glyphicon-ok"></i>', ['complete', 'id' => $model->id], [
                            'data-method' => 'post'
                        ]);
                    }
                ],
                'visibleButtons' => [
                    'complete' => function (Task $model) {
                        return !$model->isCompleted();
                    }
                ],
                'template'       => '{update} {complete} {delete}'
            ],
        ],
    ]); ?>
</div>
