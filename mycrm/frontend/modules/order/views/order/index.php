<?php

use core\helpers\HtmlHelper as Html;
use core\helpers\order\OrderConstants;
use core\models\order\Order;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\order\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Orders');
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_customers'></div><h1>{$this->title}</h1>";
$this->params['bodyID'] = 'summary';

?>
<div class="order-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= Html::beginForm("cancel", "post", ["id" => "order-grid-form"]) ?>

    <?= GridView::widget([
        'id'              => 'crud-datatable',
        'dataProvider'    => $dataProvider,
        'columns'         => [
            [
                'class'           => '\kartik\grid\CheckboxColumn',
                'checkboxOptions' => function (Order $model, $key, $index, $column) {
                    return ['disabled' => $model->isEnabled()];
                }
            ],
            'number',
            [
                'class'     => '\kartik\grid\DataColumn',
                'attribute' => 'status',
                'format'    => 'html',
                'value'     => function (Order $model) {
                    $class = "label";
                    $statusName = preg_replace('/ /', '<br>', OrderConstants::getStatuses()[$model->status], 1);
                    switch ($model->status) {
                        case OrderConstants::STATUS_ENABLED:
                            $class .= " label-warning";
                            break;
                        case OrderConstants::STATUS_DISABLED:
                            $class .= " label-danger";
                            break;
                        case OrderConstants::STATUS_FINISHED:
                            $class .= " label-success";
                            break;
                        default:
                            $class .= " label-danger";
                            break;
                    }
                    return "<span class='{$class}'>" . $statusName . "</span>";
                },
            ],
            [
                'class'          => '\kartik\grid\DataColumn',
                'label'          => Yii::t('app', 'Session'),
                'format'         => 'raw',
                'attribute'      => 'datetime',
                'value'          => function (Order $model) {
                    return Yii::$app->formatter->asDate($model->datetime) . "<br>" .
                        Yii::$app->formatter->asTime($model->datetime);
                },
                'contentOptions' => ['class' => 'nowrap'],
            ],
            [
                'class'     => '\kartik\grid\DataColumn',
                'attribute' => 'company_customer_id',
                'format'    => 'html',
                'value'     => function (Order $model) {
                    $customer = $model->companyCustomer->customer;
                    $info = [
                        $customer->getFullName(),
                        $customer->phone,
                    ];

                    if (isset($customer->iin)) {
                        $info[] = 'Иин: ' . $customer->iin;
                    }
                    if (isset($customer->id_card_number)) {
                        $info[] = 'Номер карты: ' . $customer->id_card_number;
                    }

                    return implode(",<br>", $info);
                }
            ],
            [
                'class'     => '\kartik\grid\DataColumn',
                'format'    => 'html',
                'attribute' => 'staff_id',
                'value'     => function (Order $model) {
                    return $model->staff->getFullName();
                }
            ],
            [
                'class'  => '\kartik\grid\DataColumn',
                'format' => 'html',
                'label'  => Yii::t('app', 'Services'),
                'value'  => function (Order $model) {
                    return $model->getServicesTitle("<hr>");
                }
            ],
            [
                'format'    => 'html',
                'label'     => Yii::t('app', 'Created By'),
                'attribute' => 'created_time',
                'value'     => function (Order $model) {
                    $result = [];

                    if ($model->type == OrderConstants::TYPE_MANUAL) {
                        if ($model->createdUser->staff) {
                            $result[] = $model->createdUser->staff->getFullName();
                        }
                    } elseif ($model->type == OrderConstants::TYPE_APPLICATION) {
                        $result[] = Yii::t('app', 'application');
                    }

                    $result[] = Yii::$app->formatter->asDate($model->created_time);
                    $result[] = Yii::$app->formatter->asTime($model->created_time);

                    return implode('<br>', $result);
                }
            ],
            [
                'attribute' => 'price',
                'format'    => 'decimal',
                'label'     => Yii::t('app', 'Price'),
                'hAlign'    => 'right',
                'pageSummary' => Yii::$app->formatter->asDecimal($dataProvider->query->sum('{{%orders}}.price') ?: 0)
            ],
            [
                'format'      => 'decimal',
                'label'       => Yii::t('app', 'Paid'),
                'value'       => function (Order $model) {
                    return $model->getPaidTotal();
                },
                'pageSummary' => Yii::$app->formatter->asDecimal($dataProvider->query->sum('{{%orders}}.price + {{%orders}}.payment_difference') ?: 0)
            ],
            [
                'attribute' => 'payment_difference',
                'format'    => 'decimal',
                'label'     => 'Разница',
                'hAlign'    => 'right',
                'pageSummary' => Yii::$app->formatter->asDecimal($dataProvider->query->sum('{{%orders}}.payment_difference') ?: 0)
            ],
            [
                'attribute' => 'note',
                'label'     => Yii::t('app', 'Comments')
            ],
            [
                'attribute' => 'companyCustomer.source_id',
                'value'     => function(Order $model) {
                    if ($model->companyCustomer->source_id) {
                        $model->companyCustomer->source->name;
                    }
                    return null;
                },
            ],
        ],
        'striped'         => true,
        'responsive'      => true,
        'responsiveWrap'  => false,
        'showPageSummary' => true,
        'summary'         => Html::getSummary(),
    ]) ?>

    <?= Html::endForm() ?>

</div>