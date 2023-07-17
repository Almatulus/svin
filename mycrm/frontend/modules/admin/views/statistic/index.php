<?php

use kartik\daterange\DateRangePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;


/* @var $this yii\web\View */

/* @var $model \frontend\modules\admin\forms\StatisticForm */
/* @var $statisticsData \core\services\dto\AdminStatisticsData */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Statistic') . ' - ' . Yii::t('app', 'General');

$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>', 'label' => Yii::t('app', 'Statistic'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'General');

?>

    <div class="statistic">
        <div class="stat-general">

            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
                'fieldConfig' => [
                    'template' => "{input}\n{hint}\n{error}"
                ]
            ]); ?>

            <div class="row">
                <div class="col-md-12">
                    <h3><?= Yii::t('app', 'Filters') ?></h3>
                </div>
                <div class="col-md-6">
                    <?php
                    echo $form->field($model, 'date_range', [
                        'options'=>['class'=>'drp-container form-group']
                    ])->widget(DateRangePicker::classname(), [
                        'useWithAddon'=>false,
                        'convertFormat'=>true,
                        'presetDropdown'=>true,
                        'hideInput'=>true,
                        'pluginOptions'=>[
                            'timePicker'=>false,
                            'locale'=>['format' => 'Y-m-d'],
                        ]
                    ]);
                    ?>
                </div>
                <div class="col-md-1">
                    <div class="form-group pull-right">
                        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info" data-toggle="tooltip"
                               title='Суммируется все поступления'></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Активные компании</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->activeCompaniesCount) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info"></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Активные сотрудники</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->activeStuffsCount); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info"></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Доход компании</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->income); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info" data-toggle="tooltip"
                               title='Суммируется все поступления'></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Пациенты компании</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->totalCustomersCount) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info"></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Отправленные сообщения</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->sentSmsCount); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info"></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Созданные записи</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->totalOrdersCount); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info"></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Завершенные записи</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->finishedOrdersCount); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info"></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">Отмененные записи</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <?= Yii::$app->formatter->asDecimal($statisticsData->totalOrdersCount - $statisticsData->finishedOrdersCount); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="portlet mt-element-ribbon light portlet-fit bordered">
                        <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                            <div class="ribbon-sub ribbon-bookmark"></div>
                            <i class="fa fa-info"></i>
                        </div>
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject bold uppercase">График созданных записей</span>
                            </div>
                        </div>
                        <div class="portlet-body" style="height: 200px;">
                                <div id="chart" style="height: 200px;"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-12">
                    <?= \yii\grid\GridView::widget([
                        'dataProvider' => $dataProvider,
                        'layout'       => "{summary}\n<div class='table-responsive'>{items}</div>\n{pager}",
                        'formatter'    => [
                            'class'                  => 'yii\i18n\Formatter',
                            'nullDisplay'            => 0,
                            'numberFormatterSymbols' => [
                                NumberFormatter::CURRENCY_SYMBOL => '₸',
                            ],
                        ],
                        'columns'      => [
                            'name',
                            'income:currency:Доход',
                            'staff_count:integer:Кол-во сотрудников',
                            'staff_in_schedule:integer:Кол-во сотрудников в графике',
                            'orders_count:integer:Кол-во созданных записей',
                            'finished:integer:Кол-во завершенных записей',
                            'canceled:integer:Кол-во отмененных записей',
                            'sms_count:integer:Кол-во отправленных сообщений',
                            'customers_count:integer:Кол-во клиенткой базы',
                        ]
                    ]) ?>
                </div>
            </div>

        </div>
    </div>

<?php
$range = json_encode(array_merge(['x'], $statisticsData->range));
$orders = json_encode(array_merge(['созданные записи'], $statisticsData->rangedOrders));

$this->registerJs("
    var chart = c3.generate({
        bindto: '#chart',
        data: {
            x: 'x',
            colors: {'созданные записи': '#58C9F8'},
            columns: [
                $range,
                $orders,
            ],
            types: {
                'созданные записи': 'area',
            },
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    fit: false,
                    centered: true,
                    outer: false,
                    count: 10,
                    culling: true,
                    format: '%d.%m'
                },
            },
            y: {
                inner: false,
                tick: {
                    count: 5,
                    format: d3.format('.0f')
                }
            }
        },
        legend: {
            show: false
        },
        point: {
            show: false
        }
    });
");
?>