<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\ManufacturerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Manufacturers');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 
    'label' => $this->title, 
    'url' => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="manufacturer-index">
    <div class="column_row row buttons-row">
        <div class="col-xs-12 right-buttons">
            <?= Html::a(Yii::t('app', 'Add manufacturer'), ['create'], ['class' => 'btn btn-primary']); ?>
        </div>
    </div>
    <div class="column_row">
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_item',
            'layout' => "{items}\n{pager}",
        ]);
        ?>
    </div>
</div>