<?php

namespace core\services\company;

use common\components\excel\Excel;
use common\components\excel\ExcelFileConfig;
use common\components\excel\ExcelRow;
use core\models\CompanyPaymentLog;
use yii\helpers\ArrayHelper;

class PaymentService
{
    /** @var Excel */
    private $excel;

    /**
     * PaymentService constructor.
     * @param Excel $excel
     */
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    /**
     * @param array $models
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export(array $models)
    {
        $filename = \Yii::t('app', "Pay account") . "_" . date("d-m-Y-His");
        $creator = \Yii::$app->name;
        $title = \Yii::t('app', "Pay account");

        $header = new ExcelRow([
            \Yii::t('app', 'Created Time'),
            \Yii::t('app', 'Value'),
            \Yii::t('app', 'Description'),
            \Yii::t('app', 'Status'),
        ], true);

        $columns = [
            CompanyPaymentLog::class => [
                'created_time',
                'value' => function ($model) {
                    return \Yii::$app->formatter->asDecimal($model->value);
                },
                'description',
                'statusLabel'
            ]
        ];

        $data = array_map(function ($model) use ($columns) {
            return new ExcelRow(ArrayHelper::toArray($model, $columns));
        }, $models);

        array_unshift($data, $header);

        $this->excel->generateReport(
            new ExcelFileConfig(
                $filename,
                $creator,
                $title
            ),
            $data
        );
    }
}