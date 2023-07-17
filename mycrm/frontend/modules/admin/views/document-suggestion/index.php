<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\admin\search\DocumentSuggestionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Document Suggestions');
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_customers'></div><h1>{$this->title}</h1>";
$this->params['bodyID']        = 'summary';
?>
<div class="document-suggestion-index">

<?php echo $this->render('_search', ['model' => $searchModel]); ?>

<?= GridView::widget([
    'id' => 'crud-datatable',
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        [
            'attribute' => 'text',
            'format' => 'html',
            'value' => function ($model) {
                return Html::a($model->text, ['update', 'id' => $model->id]);
            },
        ],

        [
            'class' => 'yii\grid\ActionColumn',
            'template' => "{delete}",
        ],
    ],
    'striped' => true,
    'responsive' => true,
    'responsiveWrap' => false,
]) ?>

</div>
