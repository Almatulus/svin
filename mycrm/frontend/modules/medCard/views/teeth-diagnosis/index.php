<?php

use core\models\medCard\MedCardToothDiagnosis;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\medCard\search\MedCardToothDiagnosisSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Med Card Teeth Diagnoses');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="med-card-teeth-diagnosis-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create'],
            ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [

            [
                'attribute' => 'name',
                'format'    => 'html',
                'value'     => function (MedCardToothDiagnosis $model) {
                    return Html::a(
                        $model->name,
                        ['update', 'id' => $model->id]
                    );
                }
            ],
            'abbreviation',
            [
                'attribute' => 'color',
                'format'    => 'html',
                'value'     => function (MedCardToothDiagnosis $model) {
                    return Html::tag(
                        'div',
                        $model->color,
                        [
                            'style' => [
                                'background-color' => $model->color,
                                'border'           => '1px solid #000'
                            ]
                        ]
                    );
                }
            ],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}'],
        ],
    ]); ?>
</div>
