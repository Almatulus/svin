<?php

use core\models\company\CompanyPosition;
use core\models\division\Division;
use core\models\Staff;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\StaffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                      = Yii::t('app', 'Staff');
$this->params['breadcrumbs'][]    = [
    'template' => '<li class="active"><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $this->title
];
$this->params['mainContentClass'] = 'settings';
$this->params['innerClass']       = 'employee_list';
?>

<div class="staff-index">

    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'showHeader'   => false,
        'dataProvider' => $dataProvider,
        'options'      => ['style' => 'overflow-x: auto'],
        'tableOptions' => ['class' => 'table table-condensed'],
        'layout'       => "{items}\n{pager}",
        'columns'      => [
            [
                'format'         => 'html',
                'attribute'      => 'avatar',
                'value'          => function (Staff $model) {
                    $avatar = Html::img($model->getAvatarImageUrl(), ['alt' => $model->getFullName()]);

                    return Html::a(
                        $avatar,
                        ['view', 'id' => $model->id],
                        ['class' => $model->color.' image']
                    );
                },
                'contentOptions' => ['class' => 'color avatar'],
            ],
            [
                'format'    => 'html',
                'attribute' => 'name',
                'value'     => function (Staff $model) {
                    return Html::a("<strong>{$model->getFullName()}</strong>",
                        ['view', 'id' => $model->id]);
                },
            ],
            [
                'format'         => 'html',
                'attribute'      => 'companyPositions',
                'contentOptions' => ['class' => 'role'],
                'value' => function (Staff $model) {
                    if($model->companyPositions) {
                        return implode(
                            '<hr/>',
                            ArrayHelper::getColumn(
                                $model->companyPositions,
                                function (CompanyPosition $companyPosition) {
                                    return $companyPosition->name;
                                }
                            )
                        );
                    }
                    return null;
                },
            ],
            [
                'format'         => 'html',
                'value'          => function (Staff $model) {
                    return implode(
                        '<hr/>',
                        ArrayHelper::getColumn(
                            $model->divisions,
                            function (Division $division) {
                                return $division->getTotalName();
                            }
                        )
                    );
                },
                'contentOptions' => ['class' => 'role'],
            ],
            [
                'format'         => 'html',
                'attribute'      => 'phone',
                'value'          => function (Staff $model) {
                    return '<span class="icon sprite-employed_telephone"></span> '
                           . Html::a($model->phone, 'tel:' . $model->getPlainPhone());
                },
                'contentOptions' => ['class' => 'phone'],
            ],
        ],
    ]); ?>
</div>
