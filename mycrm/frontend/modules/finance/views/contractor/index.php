<?php

use core\models\finance\CompanyContractor;
use kartik\grid\GridView;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app','Contractors');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => $this->title];
?>
<div class="company-contractor-index">

    <p>
        <?= Html::a(Yii::t('app','Create Contractor'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label' => '',
                'format' => 'html',
                'hAlign' => 'center',
                'value' => function(CompanyContractor $model) {
                    $tag = '';
                    switch($model->type) {
                        case CompanyContractor::TYPE_LEGAL:
                            $tag = 'building-o';
                            break;
                        case CompanyContractor::TYPE_PHYSICAL:
                            $tag = 'male';
                            break;
                        case CompanyContractor::TYPE_SOLE_TRADER:
                            $tag = 'truck';
                            break;
                        default:
                    }
                    return FA::icon($tag,[
                        'class' => 'fa-2x'
                    ]);
                }
            ],
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function(CompanyContractor $model) {
                    $title = $model->name . ($model->comments ? "<br><small>" . $model->comments . "</small>" : '');
                    return Html::a($title, ['update', 'id' => $model->id]);
                }
            ],
            [
                'attribute' => 'phone',
                'label' => Yii::t('app', 'Contacts'),
                'format' => 'html',
                'value' => function(CompanyContractor $model) {
                    return $model->phone."<br><small>".$model->email."</small>";
                }
            ],
            'contacts',
            [
                'attribute' => 'iin',
                'label' => Yii::t('app', 'Requisites'),
                'format' => 'html',
                'value' => function(CompanyContractor $model) {
                    return "<small>ИИН: ".$model->iin."</small><br><small>КПП: ".$model->kpp."</small>";
                }
            ],
            'address',
//            [
//                'label' => Yii::t('app', 'Balance'),
//                'value' => function(CompanyContractor $model) {
//                    return '';
//                }
//            ],
            ['class' => 'yii\grid\ActionColumn', 'template'=>'{delete}'],
        ],
    ]); ?>

</div>
