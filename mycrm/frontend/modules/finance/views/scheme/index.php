<?php

use core\models\finance\Payroll;
use core\models\Staff;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Payroll Schemes');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_extensions"  style="top: -3px"></div>{link}</li>',
    'label'    => '<h1>' . $this->title . '</h1>'
];
?>
<style>
    .payroll-caption {
        background: #f5f8fa;
        vertical-align: middle;
        border: 1px solid #ddd;
        padding: 8px;
        line-height: 1.42857143;
    }

    .payroll-caption h5 {
        display: inline-block;
        font-size: 14px;
        margin: 5px 0 7px;
        padding: 0;
        text-overflow: ellipsis;
        float: left;
    }
</style>
<div class="payroll-scheme-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'caption' => '<h5>Список схем</h5>' .
            Html::a(Yii::t('app', 'Create Payroll Scheme'), ['create'], ['class' => 'btn btn-default btn-xs pull-right']) . '',
        'captionOptions' => ['class' => 'payroll-caption'],
        'showHeader' => false,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function ($data)
                {
                    return Html::a($data->name, ["update", "id" => $data->id]);
                }
            ],
            [
                'label' => '',
                'format' => 'html',
                'contentOptions' => ['class' => 'color avatar'],
                'options' => ['width' => '40%'],
                'value' => function(Payroll $model) {
                    $result = '';
                    /* @var $staff Staff */
                    foreach($model->staffs as $staff) {
                        $image = $staff->image_id ? Html::img($staff->getAvatarImageUrl(), ['alt' => $staff->getFullName()]) : '';
                        if (!empty($image)) {
                            $image = Html::a($image, ['/staff/view', 'id' => $staff->id], ['class' => $staff->color . ' image']);
                        }
                        $staff_name = Html::a("{$staff->getFullName()}", ['/staff/view', 'id' => $staff->id]);
                        $result .= Html::tag('div', $image . $staff_name, ['class' => 'inline_block text-center']);
                    }
                    return $result;
                }
            ],
            [
                'attribute' => 'service_value',
                'format' => 'raw',
                'label' => 'За услуги',
                'value' => function(Payroll $model) {
                    return 'За услуги<br>' . number_format($model->service_value, 0, '', ' ') . ' ' . Payroll::getModeLabels()[$model->service_mode];
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
            ],
        ],
    ]); ?>

</div>
