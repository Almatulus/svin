<?php

namespace tests\unit\services\company;

use core\models\company\Company;
use core\models\CompanyPaymentLog;
use core\services\company\PaymentService;

class PaymentTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var PaymentService
     */
    private $service;

    /**
     * @var Company
     */
    private $company;

    /**
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function testExport()
    {
        $payments = $this->tester->getFactory()->seed(10, CompanyPaymentLog::class, [
            'company_id' => $this->company->id
        ]);

        $this->service->export($payments);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        if ( ! $this->company) {
            $this->company = $this->tester->getFactory()->create(Company::class);
        }

        $this->service = \Yii::createObject(PaymentService::class);
    }

    protected function _after()
    {
    }

}