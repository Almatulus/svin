<?php

use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Customers');
$this->params['breadcrumbs'][] = $this->title;

$header = "<tr><th>".Yii::t('app','Sent')."</th><th>".Yii::t('app','Type')."</th><th>".Yii::t('app','Status')."</th><th>".Yii::t('app','Updated')."</th></tr>";
?>
<div class="customer-request-history">
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'customers',
        'options' => [
            'class' => 'customers-table kv-grid-table table table-bordered table-striped kv-table-wrap',
            'tag' => 'table',
            'id' => 'list-wrapper',
        ],
        'layout' => $header."\n{pager}\n{items}\n{summary}",
        'itemView' => '_request_item',
    ]); ?>
</div>