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
/* @var $category_id integer */

$this->title                   = Yii::t('app', 'Services');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_services"></div>{link}</li>', 'label' => $this->title, 'url' => ['index']];
?>

<div class="service-index">

    <div class="column_row row buttons-row">
        <div class="col-sm-8">
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
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
        <?php if (Yii::$app->user->can("divisionServiceCreate")): ?>
            <div class="col-sm-4 right-buttons">
            <div class="dropdown inline_block">
                <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
                    Действия <b class="caret"></b>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Export'),
                            [
                                'export',
                                'DivisionServiceSearch[category_id]'          => $category_id,
                                'DivisionServiceSearch[name]'                 => $searchModel->name,
                                'DivisionServiceSearch[division_id]'          => $searchModel->division_id,
                                'DivisionServiceSearch[insurance_company_id]' => $searchModel->insurance_company_id
                            ]
                        ) ?>
                    </li>
                    <li>
                        <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Download template'), '#', ['id' => 'js-download-template']) ?>
                    </li>
                    <li>
                        <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Import from Excel'), '#', ['id' => 'js-import',]) ?>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li>
                        <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Archive'), \yii\helpers\Url::to(['archive']), ['id' => 'js-import',]) ?>
                    </li>
                </ul>
            </div>
            <?= Html::a(Yii::t('app', 'Add company service'), ['create'], ['class' => 'btn btn_blue']) ?>
        </div>
        <?php endif; ?>
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
                    if (Yii::$app->user->can("divisionServiceUpdate", ["model" => $model])) {
                        return Html::a($model->service_name, ['update', 'id' => $model->id]);
                    } else {
                        return $model->service_name;
                    }
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
                'class'          => 'yii\grid\ActionColumn',
                'template'       => '{delete}',
                'buttons'        => [
                    'delete' => function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ["delete", "id" => $model->id],
                            [
                                'title' => Yii::t('app', 'Delete'),
                                'data'  => [
                                    'confirm' => Yii::t('yii',
                                        'Are you sure you want to delete this item?'),
                                    'method'  => 'post',
                                ],
                            ]
                        );
                    }
                ],
                'visibleButtons' => [
                    'delete' => function ($model) {
                        return Yii::$app->user->can("divisionServiceDelete",
                            ['model' => $model]);
                    }
                ]
            ],
        ],
    ]); ?>
</div>
