<?php

use core\helpers\HtmlHelper as Html;
use core\models\division\Division;
use frontend\modules\division\search\DivisionServiceSearch;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $division Division */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel DivisionServiceSearch */

$this->title                   = Yii::t('app', 'Archive');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_services"></div>{link}</li>', 'label' => $this->title, 'url' => ['index']];
?>

<div class="service-index">

    <div class="column_row row buttons-row">
        <div class="col-sm-12">
            <?php $form = ActiveForm::begin([
                'action' => ['deleted'],
                'method' => 'get'
            ]);
            echo Html::activeTextInput($searchModel, 'name', [
                'class' => 'right_space',
                'placeholder' => Yii::t('app', 'Find service')]);
            echo Html::activeDropDownList($searchModel, 'division_id', Division::getOwnDivisionsNameList(),
                ['class' => 'right_space', 'prompt' => Yii::t('app', 'All Divisions')
                ]);
            echo Html::activeDropDownList($searchModel, 'insurance_company_id', \core\models\InsuranceCompany::map(),
                [
                    'class'  => 'right_space',
                    'prompt' => Yii::t('app', 'All Insurance Companies'),
                    'style'  => 'width: 180px'
                ]);
            echo Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary']);
            ActiveForm::end(); ?>
        </div>
    </div>

    <?= GridView::widget([
        'showOnEmpty'      => false,
        'dataProvider'     => $dataProvider,
        'options'          => ['class' => 'column_row data_table'],
        'tableOptions'     => ['class' => 'table table-bordered'],
        'responsiveWrap'   => false,
        'emptyTextOptions' => ['style' => 'margin-top: 60px'],
        'emptyText'        => '<div class="col-md-12">
			<div class="empty-list-icon"><i class="icon sprite-first_resource"></i></div>
			<h2 class="empty-list-heading-centered">Услуг не найдено</h2>
			</div>',
        'summary'          => Html::getSummary(),
        'columns'          => [
            [
                'format'         => 'html',
                'attribute'      => 'service_name',
                'contentOptions' => ['style' => 'max-width: 180px; white-space: normal;'],
                'value'          => function ($model) {
                    return $model->service_name;
                }
            ],
            [
                'label'          => Yii::t('app', 'Category ID'),
                'format'         => 'html',
                'contentOptions' => ['width' => '20%'],
                'value'          => function ($model) {
                    return implode(", <br/>", ArrayHelper::getColumn($model->categories, "name"));
                }
            ],
            [
                'attribute' => 'is_trial',
                'format'    => 'boolean',
                'hAlign'    => 'center',
            ],
            [
                'attribute' => 'average_time',
                'value'     => function ($model) {
                    return gmdate("H:i", $model->average_time * 60);
                }
            ],
            [
                'attribute' => 'divisions',
                'value'     => function ($model) {
                    return implode(", ", ArrayHelper::getColumn($model->divisions, 'name'));
                }
            ],
            [
                'attribute' => 'price',
                'format'    => 'decimal',
                'hAlign'    => 'right',
            ],
            [
                'attribute' => 'price_max',
                'format'    => 'decimal',
                'hAlign'    => 'right',
            ],
            [
                'attribute' => 'insurance_company_id',
                'value'     => 'insuranceCompany.name'
            ],
            [
                'class'    => 'yii\grid\ActionColumn',
                'buttons'  => [
                    'restore' => function ($url, $model, $key) {
                        return Html::a(Yii::t('app', 'Restore'),
                            ['restore', 'id' => $model->id],
                            [
                                'class'        => 'btn btn-default',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to restore this service?'),
                                'data-method'  => 'post',
                            ]
                        );
                    }
                ],
                'template' => '{restore}'
            ],
        ],
    ]); ?>
</div>
