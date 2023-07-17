<?php
use core\models\order\Order;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Customers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-history">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'customers',
    'options' => [
        'class' => 'customers-table',
    ],
    'columns' => [
        [
            'value' => function(Order $model) {
                return $model->status;
            }
        ],
        [
            'label' => Yii::t('app', 'Staff/data'),
            'format' => 'html',
            'value' => function(Order $model) {
                $name = "<b>{$model->staff->getFullName()}</b>";
                $date = $model->datetime;
                return $name.'<br>'.$date;
            }
        ],
        [
            'label' => Yii::t('app', 'Service'),
            'value' => function(Order $model) {
                return $model->divisionService->service->name;
            }
        ],
        [
            'label' => Yii::t('app', 'Price currency'),
            'value' => function(Order $model) {
                return $model->price;
            }
        ],
        [
            'label' => Yii::t('app', 'Discount, %'),
            'value' => function(Order $model) {
                return $model->discount;
            }
        ],
        [
            'label' => Yii::t('app', 'Total currency'),
            'value' => function(Order $model) {
                return $model->price;
            }
        ],
    ],
]); ?>
</div>