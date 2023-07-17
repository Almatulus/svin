<?php

use core\helpers\division\DivisionHelper;
use core\models\division\Division;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\division\search\DivisionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Divisions');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="division-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="form-group">
        <?= Html::a(Yii::t('app', 'Create'), ['/division/division/create'], ['class' => 'btn btn-default']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [
            [
                'attribute' => 'status',
                'format'    => 'html',
                'value'     => function ($data) {
                    $class = "label";
                    switch ($data->status) {
                        case Division::STATUS_ENABLED:
                            $class .= " label-success";
                            break;
                        case Division::STATUS_DISABLED:
                            $class .= " label-danger";
                            break;
                    }

                    return "<span class='{$class}'>"
                           . DivisionHelper::getStatusLabel($data->status) . "</span>";
                }
            ],
            [
                'attribute' => 'name',
                'format'    => 'html',
                'value'     => function ($data) {
                    return Html::a($data->name, ["update", "id" => $data->key]);
                }
            ],
            'company.name',
            'city.name',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}'],
        ],
    ]); ?>
</div>
