<?php

use core\models\company\Company;
use core\models\user\User;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\admin\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][]
             = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div><h1>{link}</h1></li>',
    'label'    => $this->title
];
?>
<div class="user-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create'],
            ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', Yii::$app->user->identity->username),
            ['update', 'id' => Yii::$app->user->id],
            ['class' => 'btn btn-default pull-right']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'rowOptions'   => function (User $user) {
            return $user->isDisabled() ? ['class' => 'danger'] : [];
        },
        'columns'      => [
            [
                'attribute' => 'username',
                'format'    => 'html',
                'filter'    => \yii\widgets\MaskedInput::widget([
                    'name' => 'username',
                    'mask' => '+7 999 999 99 99',
                ]),
                'value'     => function ($model) {
                    return Html::a($model->username,
                        ['update', 'id' => $model->id]);
                }
            ],
            [
                'attribute' => 'company_id',
                'filter'    => \kartik\select2\Select2::widget([
                    'name' => 'company_id',
                    'value' => $searchModel->company_id,
                    'data' => ArrayHelper::map(Company::find()->all(), 'id', 'name'),
                    'options' => [
                            'prompt' => Yii::t('app', 'Select company')
                    ],
                ]),
                'value'     => function (User $model) {
                    return $model->company->name;
                }
            ],
            [
                'label'  => Yii::t('app', 'Staff ID'),
                'format' => 'html',
                'value'  => function (User $model) {
                    return isset($model->staff) ? Html::a($model->staff->getFullName(),
                        ['/staff/view', 'id' => $model->staff->id]) : null;
                }
            ],
            [
                'class' => '\kartik\grid\DataColumn',
                'attribute' => 'status',
                'format' => 'html',
                'value' => function (User $model) {
                    $class = "label";
                    $statusName = preg_replace('/ /', '<br>', \core\helpers\user\UserHelper::getStatuses()[$model->status], 1);
                    switch ($model->status) {
                        case User::STATUS_DISABLED:
                            $class .= " label-warning";
                            break;
                        case User::STATUS_ENABLED:
                            $class .= " label-success";
                            break;
                    }
                    return "<span class='{$class}'>" . $statusName . "</span>";
                },
            ],
        ],
    ]); ?>

</div>
