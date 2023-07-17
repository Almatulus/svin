<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\StocktakeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Stocktakes');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label' => $this->title,
    'url' => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="stocktake-index">

    <div class="column_row row buttons-row">
        <div class="col-sm-12 right-buttons">
            <?= Html::a(Yii::t('app', 'New stocktake'), ['create'], ['class' => 'btn btn-primary pull-right']) ?>
        </div>
    </div>

    <?php
    if ($currentStocktake) { ?>
        <h5>ТЕКУЩАЯ ИНВЕНТАРИЗАЦИЯ</h5>
        <div class="column_row data_table">
            <table data-selectable="">
                <thead>
                    <tr>
                    <th class="wrap"><?= $currentStocktake->getAttributeLabel('title') ?></th>
                    <th class="wrap"><?= $currentStocktake->getAttributeLabel('numberOfProducts') ?></th>
                    <th></th>
                    </tr>
                    </thead>
                <tbody>
                    <tr>
                    <td class="link_body blue_text">
                        <?= Html::a($currentStocktake->title, $currentStocktake->link); ?>
                    </td>
                    <td><?= $currentStocktake->numberOfProducts ?></td>
                    <td class="link_body text_center">
                        <?= Html::a("продолжить инветаризацию", $currentStocktake->link); ?>
                    </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <h5>ИСТОРИЯ</h5>
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'title',
                    'format' => 'html',
                    'value' => function($model) {
                        return Html::a($model->title, ['view', 'id' => $model->id]);
                    }
                ],
                'numberOfProducts',
                'productsWithShortageCount',
                'productsWithSurplusCount',
                'accurateProductsCount'
            ],
        ]);
    ?>
</div>
