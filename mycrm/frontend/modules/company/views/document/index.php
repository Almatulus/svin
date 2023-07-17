<?php

use core\models\order\OrderDocumentTemplate;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\company\search\CompanyDocumentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Company Documents');
$this->params['breadcrumbs'][]
             = "<i class='fa fa-envelope'></i>&nbsp;<h1>{$this->title}</h1>";
?>
<div class="company-document-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create'],
            ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            ['class'    => 'yii\grid\ActionColumn',
             'template' => '{update} {delete}'],
        ],
    ]); ?>
</div>
