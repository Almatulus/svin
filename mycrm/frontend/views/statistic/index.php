<?php

use core\helpers\DateHelper;
use core\models\division\Division;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use miloschuman\highcharts\Highcharts;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;


/* @var $this yii\web\View */

/* @var $model core\forms\customer\StatisticForm */
/* @var $prevStat core\forms\customer\StatisticForm */
/* @var $canceledCount */
/* @var $confirmedCount */
/* @var $successCount */
/* @var $pendingCount */

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
            <div class="col-md-2">
                <?=
                $form->field($model, 'from', [
                    'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'From date') . '</span>{input}</div>',
                ])->widget(DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ]) ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, 'to', [
                    'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'To date') . '</span>{input}</div>',
                ])->widget(DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'division')->widget(Select2::className(), [
                    'data' => Division::getOwnDivisionsNameList(),
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Divisions')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language' => 'ru'
                    ],
                    'showToggleAll' => false,
                    'theme' => Select2::THEME_CLASSIC,
                ])
                ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'staff')->widget(Select2::className(), [
                    'data' => \core\forms\customer\statistic\StatisticStaff::getOwnCompanyStaffList(),
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Staff')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language' => 'ru'
                    ],
                    'showToggleAll' => false,
                    'theme' => Select2::THEME_CLASSIC,
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'user')->widget(Select2::classname(), [
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Customer')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => 'ru',
                        'ajax' => [
                            'url' => ['/customer/customer/user-list'],
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(user) { return user.text; }'),
                        'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                    ],
                    'theme' => Select2::THEME_CLASSIC
                ])
                ?>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="pull-left">
                    <?php
                    $datetimeTo   = new DateTime($model->to);
                    $datetimeFrom = new DateTime($model->from);
                    $difference   = $datetimeTo->diff($datetimeFrom)->days;
                    echo sprintf('Выбран период длительностью %d суток', $difference);
                    ?>
                </div>

                <div class="form-group pull-right">
                    <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <div class="row">
            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Суммируется все поступления'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase">Доход</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= Html::a(Yii::$app->formatter->asDecimal($model->getIncome()),
                            \yii\helpers\Url::to([
                                '/finance/cashflow/index',
                                'sFrom' => $model->from,
                                'sTo' => $model->to,
                                'sStaff' => $model->staff,
                                'sCustomer' => $model->user,
                                'sDivision' => $model->division,
                                'sCost' => -1,
                            ])) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Суммируется все отчисления'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase">Расход</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= Html::a(Yii::$app->formatter->asDecimal($model->getExpense()),
                            \yii\helpers\Url::to([
                                '/finance/cashflow/index',
                                'sFrom' => $model->from,
                                'sTo' => $model->to,
                                'sStaff' => $model->staff,
                                'sCustomer' => $model->user,
                                'sDivision' => $model->division,
                                'sCost' => -2,
                            ])) ?>
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
                            <span class="caption-subject bold uppercase">Прибыль</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= Html::a(Yii::$app->formatter->asDecimal($model->getProfit()),
                            \yii\helpers\Url::to([
                                '/finance/cashflow/index',
                                'sFrom' => $model->from,
                                'sTo' => $model->to,
                                'sStaff' => $model->staff,
                                'sCustomer' => $model->user,
                                'sDivision' => $model->division
                            ])) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Вычисляется по записям со статусом "Клиент пришел"'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase">Средний чек</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= Yii::$app->formatter->asDecimal($model->averageRevenue) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Суммарная продолжительность записей со статусом "Клиент пришел" отнесенная к суммарной записи продолжительности рабочего дня сотрудников'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase">Заполняемость</span>
                        </div>
                    </div>
                    <div class="portlet-body"><?= number_format($model->occupancy * 100, 2, '.', ' ') . '%'; ?></div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12">
                <div id="chart" style="height: 200px;"></div>
            </div>
        </div>

        <div class="row text-center">

            <div class="col-md-3">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Общее количество записей, в том числе отмененных'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase">Всего записей</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?= Html::a($model->totalCount,
                            \yii\helpers\Url::to([
                                '/order/order/index',
                                'from_date' => $model->from,
                                'to_date' => $model->to
                            ])) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Количество отмененных записей + количество записей со статусом "Клиент не пришел"'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                        <span class="caption-subject bold uppercase">
                            Отмененных
                        </span>
                        </div>
                    </div>
                    <div class="portlet-body"><?= $model->disabledCount ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Количество записей со статусом "Клиент пришел"'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                        <span class="caption-subject bold uppercase">
                            Завершенных
                        </span>
                        </div>
                    </div>
                    <div class="portlet-body"><?= $model->finishedCount ?></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="ribbon ribbon-vertical-right ribbon-shadow ribbon-color-primary uppercase">
                        <div class="ribbon-sub ribbon-bookmark"></div>
                        <i class="fa fa-info" data-toggle="tooltip"
                           title='Количество записей со статусом "Клиент подтвердил" и "Ожидание клиента"'></i>
                    </div>
                    <div class="portlet-title">
                        <div class="caption">
                        <span class="caption-subject bold uppercase">
                            Незавершенных
                        </span>
                        </div>
                    </div>
                    <div class="portlet-body"><?= $model->enabledCount ?></div>
                </div>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-md-6">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase">Структура записей по источникам</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?php
                        echo Highcharts::widget([
                            'options' => [
                                'chart' => [
                                    'plotBackgroundColor' => null,
                                    'plotBorderWidth' => null,
                                    'plotShadow' => false,
                                    'type' => 'pie',
                                    'height' => '200'
                                ],
                                'tooltip' => [
                                    'pointFormat' =>
                                        'Количество: <b>{point.y}</b><br/>' .
                                        'Процент: <b>{point.percentage:.1f}%</b>'
                                ],
                                'plotOptions' => [
                                    'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => false
                                        ],
                                        'showInLegend' => true
                                    ]
                                ],
                                'title' => false,
                                'series' => [
                                    [
                                        'name' => 'Количество',
                                        'data' => $model->getOrdersByTypes()
                                    ],
                                ]
                            ]
                        ]);
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span
                                class="caption-subject bold uppercase">Откуда клиенты узнают о вас</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?php
                        echo Highcharts::widget([
                            'options' => [
                                'chart' => [
                                    'plotBackgroundColor' => null,
                                    'plotBorderWidth' => null,
                                    'plotShadow' => false,
                                    'type' => 'pie',
                                    'height' => '200'
                                ],
                                'tooltip' => [
                                    'pointFormat' =>
                                        'Количество: <b>{point.y}</b><br/>' .
                                        'Процент: <b>{point.percentage:.1f}%</b>'
                                ],
                                'plotOptions' => [
                                    'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => false
                                        ],
                                        'showInLegend' => true
                                    ],
                                ],
                                'title' => false,
                                'series' => [
                                    [
                                        'name' => 'Количество',
                                        'data' => $model->getCustomersBySource()
                                    ],
                                ]
                            ]
                        ]);
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase">
                                Кто создал запись
                            </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?php
                        echo Highcharts::widget([
                            'options' => [
                                'chart' => [
                                    'plotBackgroundColor' => null,
                                    'plotBorderWidth' => null,
                                    'plotShadow' => false,
                                    'type' => 'pie',
                                    'height' => '200'
                                ],
                                'tooltip' => [
                                    'pointFormat' =>
                                        'Количество: <b>{point.y}</b><br/>' .
                                        'Процент: <b>{point.percentage:.1f}%</b>'
                                ],
                                'plotOptions' => [
                                    'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => false
                                        ],
                                        'showInLegend' => true
                                    ],
                                ],
                                'title' => false,
                                'series' => [
                                    [
                                        'name' => 'Количество',
                                        'data' => $model->getOrdersByCreator()
                                    ],
                                ]
                            ]
                        ]);
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="portlet mt-element-ribbon light portlet-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject bold uppercase"><?=Yii::t('app', 'Return statistics')?></span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?php
                        echo Highcharts::widget([
                            'options' => [
                                'chart' => [
                                    'plotBackgroundColor' => null,
                                    'plotBorderWidth' => null,
                                    'plotShadow' => false,
                                    'type' => 'pie',
                                    'height' => '200'
                                ],
                                'tooltip' => [
                                    'pointFormat' =>
                                        Yii::t('app', 'Amount') . ': <b>{point.y}</b><br/>' .
                                        Yii::t('app', 'Percent') . ': <b>{point.percentage:.1f}%</b>'
                                ],
                                'plotOptions' => [
                                    'pie' => [
                                        'allowPointSelect' => true,
                                        'cursor' => 'pointer',
                                        'dataLabels' => [
                                            'enabled' => false
                                        ],
                                        'showInLegend' => true
                                    ]
                                ],
                                'title' => false,
                                'series' => [
                                    [
                                        'name' => Yii::t('app', 'Amount'),
                                        'data' => [
                                            [
                                                'name' => Yii::t('app', 'Returned clients'),
                                                'y' => $model->repeatedCount,
                                            ],
                                            [
                                                'name' => Yii::t('app', 'Came once'),
                                                'y' => $model->singleCount,
                                            ],
                                        ]
                                    ],
                                ]
                            ]
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$range = DateHelper::date_range($model->from, $model->to);
$revenues = $model->getRangedRevenue($range);
$range = json_encode(array_merge(['x'], $range));
$revenues = json_encode(array_merge(['доход'], $revenues));

$this->registerJs("
    var chart = c3.generate({
        bindto: '#chart',
        data: {
            x: 'x',
            colors: {'доход': '#58C9F8'},
            columns: [
                $range,
                $revenues,
            ],
            types: {
                'доход': 'area',
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