<?php
use johnitvn\ajaxcrud\CrudAsset;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app','Customer Loyalty');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => $this->title];

CrudAsset::register($this);

?>

<style>
    .grid-view tbody tr:nth-child(even) td, .kv-grid-table tbody tr:nth-child(even) td {
        background: inherit;
    }

    .table-hover > tbody > tr:hover > td, .table > tbody > tr:hover > td, .table-hover > tbody > tr:hover th, .table > tbody > tr:hover th {
        background: #fff;
    }

    .header-row {
        border-top: 1px solid #ddd !important;
    }

    .table > tbody > tr > td {
        border-top: 0;
    }

    .btn-xs {
        font-size: 10px;
        padding: 2px 6px 2px;
    }
</style>

<div>
    <?php
        echo Html::a(Yii::t('app', 'Create CustomerLoyalty'), ['create'],
            ['role'=>'modal-remote','title'=> Yii::t('app', 'Create CustomerLoyalty'),'class'=>'btn btn-default']).' '.
        Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
            ['data-pjax'=>1, 'class'=>'btn btn-default', 'title'=>'Reset Grid'])
    ?>
</div>

<div class="customer-loyalty-index">
    <div id="ajaxCrudDatatable">
        <?= GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'pjax'=>true,
            'columns' => require(__DIR__.'/_columns.php'),
            'responsive' => false,
            'striped' => false,
            'bordered' => false,
            'hover' => false,
            'layout' => "{items}\n{pager}",
            'showHeader' => false,
            'showPageSummary' => false,
        ])?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
