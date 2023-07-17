<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel core\models\warehouse\DeliverySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Deliveries');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 
    'label' => $this->title, 
    'url' => ['index']
];
?>

<?= $this->render('/common/_tabs') ?>

<div class="delivery-search">
    <div class="column_row row buttons-row">
        <div class="col-sm-12 right-buttons">
            <?= Html::a(Yii::t('app', 'Add delivery'), ['create'], ['class' => 'btn btn-primary pull-right']) ?>
        </div>
    </div>
</div>

<div class="delivery-index">
<?php Pjax::begin(); ?>
<?= \kartik\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'responsiveWrap' => false,
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'html',
            'value' => function($model) {
                return Html::a($model->name, ['update', 'id' => $model->id]);
            }
        ],
        [
            'attribute' => 'category.name',
            'label' => Yii::t('app', 'Category')
        ],
        [
            'attribute' => 'manufacturer.name',
            'label' => Yii::t('app', 'Manufacturer')
        ],
        [
            'attribute' => 'types',
            'format' => 'html',
            'value' => function($model) {
                return $model->getTypesTitle('<br>');
            }
        ],
        'sku',
        'quantity',
        'min_quantity'
    ],
]);
?>
<?php Pjax::end(); ?>
</div>
