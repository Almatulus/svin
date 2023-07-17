<?php

use core\helpers\finance\CompanyCostItemHelper;
use core\models\finance\CompanyCostItem;
use kartik\grid\GridView;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app','CostItems');
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-sign-out-alt"></i> {link}</li>',
    'label' => $this->title
];
?>
<div class="company-cost-item-index">

    <div class="column_row buttons-row">
        <div class="right-buttons">
            <?= Html::a(Yii::t('app','Create CostItem'), ['create'], ['class' => 'btn']) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label' => '',
                'format' => 'html',
                'hAlign' => 'center',
                'value' => function(CompanyCostItem $model) {
                    $icon = 'sign-out';
                    if($model->type == 0)
                        $icon = 'sign-in';
                    return FA::icon($icon,['class' => 'fa-2x']);
                }
            ],
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function(CompanyCostItem $model) {
                    return $model->getFullName();
                }
            ],
            [
                'attribute' => 'type',
                'value' => function(CompanyCostItem $model) {
                    return CompanyCostItemHelper::getTypeLabels()[$model->type];
                }
            ],
            [
                'class' => \yii\grid\ActionColumn::className(),
                'visibleButtons' => [
                    'delete' => function (CompanyCostItem $model, $key, $index) {
                        return $model->is_deletable;
                    },
                    'update' => function (CompanyCostItem $model, $key, $index) {
                        return $model->is_deletable;
                    }
                ],
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>

</div>
