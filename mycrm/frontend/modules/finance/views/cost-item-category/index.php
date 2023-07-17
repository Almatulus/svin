<?php

use core\models\finance\CompanyCostItem;
use core\models\finance\CompanyCostItemCategory;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'CostItemsCategory');
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-sign-out-alt"></i> {link}</li>',
    'label'    => $this->title
];
?>
<div class="company-cost-item-category-index">

    <div class="column_row buttons-row">
        <div class="right-buttons">
            <?= Html::a(Yii::t('app', 'Create'), ['create'],
                ['class' => 'btn']) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [
            [
                'format'    => 'html',
                'attribute' => 'name',
                'value'     => function (CompanyCostItemCategory $model) {
                    return Html::a($model->name,
                        ['update', 'id' => $model->id]);
                }
            ],
            [
                'format' => 'html',
                'value'  => function (CompanyCostItemCategory $model) {
                    return implode(", <br/>",
                        ArrayHelper::getColumn($model->costItems, "name"));
                }
            ],
            [
                'class'    => ActionColumn::class,
                'template' => '{delete}'
            ],
        ],
    ]); ?>
</div>
