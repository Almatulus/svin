<?php

use core\models\customer\CustomerSource;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\customer\search\CompanyCustomerSourceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Customer Source');
$this->params['breadcrumbs'][] = "<div class='fa fa-image'></div> <h1>{$this->title}</h1>";
?>
<div class="customer-source-index">

    <p>
        <?= Html::a(Yii::t('app', 'Add'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php try {
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'columns'      => [
                ['class' => 'yii\grid\SerialColumn'],

                'name',
                [
                    'attribute' => 'companyCustomersCount',
                    'label'     => Yii::t('app', 'Customers'),
                ],

                [
                    'class'    => 'yii\grid\ActionColumn',
                    'template' => '{move} {update} {delete}',
                    'visibleButtons' => [
                        'move'              => function (CustomerSource $model, $key, $index) {
                            return $model->type === CustomerSource::TYPE_DYNAMIC;
                        },
                        'update'              => function (CustomerSource $model, $key, $index) {
                            return true || $model->type === CustomerSource::TYPE_DYNAMIC;
                        },

                        'delete'              => function (CustomerSource $model, $key, $index) {
                            return $model->type === CustomerSource::TYPE_DYNAMIC;
                        },
                    ],
                    'buttons'  => [
                        'move' => function (
                            string $url,
                            CustomerSource $model
                        ) {
                            $icon = Html::tag(
                                'span',
                                null,
                                ['class' => 'glyphicon glyphicon-move']
                            );

                            return Html::a($icon, '#', [
                                'class'       => 'js-course-move',
                                'data-source' => $model->id,
                            ]);
                        },
                    ],
                ],
            ],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    ?>
</div>
<script>
    var sources = <?= json_encode(CustomerSource::map())?>;
    var sourcesList = [];
    for (var propertyName in sources) {
        sourcesList.push({
            text: sources[propertyName],
            value: propertyName
        });
    }
</script>
