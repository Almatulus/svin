<?php
use core\models\Staff;
use johnitvn\ajaxcrud\CrudAsset;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel \frontend\search\StaffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Последнее настроенное время расписния';
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_customers'></div><h1>{$this->title}</h1>";
$this->params['bodyID'] = 'summary';
CrudAsset::register($this);
?>
<div class="order-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'columns' => [
            [
                'label' => Yii::t('app', 'Staff name'),
                'value' => function (Staff $model) {
                    return $model->getFullName();
                }
            ],
            [
                'label' => Yii::t('app', 'Company name'),
                'value' => function (Staff $model) {
                    return $model->division->company->name;
                }
            ],
            [
                'format' => 'datetime',
                'value' => function (Staff $model) {
                    return $model->getStaffSchedules()->max('end_at');
                }
            ],
        ],
    ]); ?>

</div>