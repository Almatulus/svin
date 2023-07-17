<?php

namespace core\services\finance;

use common\components\excel\ExcelFileConfig;
use common\components\excel\ExcelRow;
use core\models\customer\CompanyCustomer;
use Yii;

class ReportService
{
    public function exportBalance(array $models){
        $titles = [
            Yii::t('app', 'Customer'),
            Yii::t('app', 'Phone'),
            Yii::t('app', 'Deposit'),
            Yii::t('app', 'Debt'),
        ];

        $rows = array_merge(
            [new ExcelRow($titles, true)],
            array_map(
                function (CompanyCustomer $model) {
                    return new ExcelRow([
                        $model->customer->getFullName(),
                        $model->customer->phone,
                        $model->getDeposit(),
                        $model->getDebt()
                    ]);
                },
                $models
            )
        );

        $filename = Yii::t('app', 'Balance report') . '_' . Yii::$app->formatter->asDatetime(new \DateTime());

        return Yii::$app->excel->generateReport(
            new ExcelFileConfig(
                $filename,
                Yii::$app->name,
                'Title'
            ),
            $rows
        );
    }
}