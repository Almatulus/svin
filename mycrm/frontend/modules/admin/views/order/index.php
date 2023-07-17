<?php
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\order\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Orders');
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_customers'></div><h1>{$this->title}</h1>";
$this->params['bodyID']        = 'summary';
?>
<div class="order-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        'columns' => require(__DIR__ . '/_columns.php'),
        'striped' => true,
        'responsive' => true,
        'responsiveWrap' => false,
    ]) ?>
</div>