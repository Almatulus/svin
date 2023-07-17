<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $searchModel \core\forms\statistic\CostPriceForm
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var array $estimatedData
 */

$this->title = Yii::t('app', 'Cost price report');

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label'    => Yii::t('app', 'Statistic'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $form = ActiveForm::begin([
    'action' => ['cost'],
    'method' => 'get'
]); ?>
    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($searchModel, 'name')->label(false)->textInput([
                'placeholder' => Yii::t('app', 'Enter name')
            ]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($searchModel, 'division_id')->widget(Select2::class,
                [
                    'data'          => \core\models\division\Division::getOwnCompanyDivisionsList(),
                    'options'       => ['placeholder' => Yii::t('app', 'All Divisions')],
                    'pluginOptions' => ['allowClear' => true],
                    'size'          => Select2::SMALL
                ])->label(false); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($searchModel, 'category_id')->widget(Select2::class,
                [
                    'data'          => \core\models\ServiceCategory::getAll(),
                    'options'       => ['placeholder' => Yii::t('app', 'All Categories')],
                    'pluginOptions' => ['allowClear' => true],
                    'size'          => Select2::SMALL
                ])->label(false); ?>
        </div>
        <div class="col-sm-3">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>


<?= \kartik\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => [
        [
            'attribute' => 'service_name',
            'format'    => 'html',
            'value' => function($data){
                return Html::a($data['service_name'], Url::to(['/division/service/update', 'id' => $data['id']]));
            }
        ],
        [
            'attribute' => 'price',
            'format'    => 'decimal',
            'hAlign'    => 'right',
        ],
        [
            'header'  => Html::a("Кол-во сотрудников<br/>со схемой ЗП", Url::to('/staff/index')),
            'value'  => function (array $data) use ($estimatedData) {
                return $estimatedData[$data['id']]['staff_count'] ?? 0;
            },
            'hAlign' => 'right',
        ],
        [
            'label'  => "Средний процент ЗП",
            'value'  => function (array $data) use ($estimatedData) {
                $result = $estimatedData[$data['id']]['avg_staff_share'] ?? 0;
                return $result . " %";
            },
            'hAlign' => 'right',
        ],
        [
            'attribute' => 'products_sum',
            'format'    => 'decimal',
            'label'     => "Сумма товаров",
            'value' => function ($model) {
                return floatval($model['products_sum']);
            },
            'hAlign'    => 'right',
        ],
        [
            'format' => 'decimal',
            'label'  => Yii::t('app', 'Cost price'),
            'value'  => function (array $data) use ($estimatedData) {
                return $estimatedData[$data['id']]['cost_price'] ?? 0;
            },
            'hAlign' => 'right',
        ]
    ]
]);