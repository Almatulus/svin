<?php

/** @var \yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;

/** @var \core\models\company\Company $company */

$this->title = Yii::t('app', 'Payments history of "{company}"', [
    'company' => $company->name
]);
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Companies'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

echo \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => [
        [
            'attribute' => 'company_id',
            'value'     => 'company.name',
        ],
        'start_date:date',
        [
            'attribute' => 'period',
            'value'     => 'interval',
        ],
        'nextPaymentDate:date',
        'sum:currency',
        'created_at:datetime',
        [
            'class'    => 'yii\grid\ActionColumn',
            'template' => '{update}',
            'buttons'  => [
                'update' => function ($url, $model, $key) {
                    $options = [
                        'title'      => \Yii::t('app', 'Update'),
                        'aria-label' => \Yii::t('app', 'Update'),
                        'data-pjax'  => '0',
                    ];
                    $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-pencil"]);
                    return Html::a($icon, ['edit-tariff-payment', 'id' => $model->id], $options);
                }
            ]
        ]
    ]
]);