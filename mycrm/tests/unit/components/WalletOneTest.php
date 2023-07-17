<?php
namespace components;

use app\tests\fixtures\CompanyFixture;
use Codeception\Specify;
use common\components\WalletOne;
use core\models\CompanyPaymentLog;

class WalletOneTest extends \Codeception\Test\Unit
{
    use Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->markTestSkipped();
        $this->tester->haveFixtures([
            'company' => [
                'class' => CompanyFixture::className(),
            ]
        ]);

        $this->_payment = new CompanyPaymentLog();
    }

    // tests
    public function testComponent()
    {
        $this->_payment->attributes = [
            'company_id'   => 3,
            "currency"     => CompanyPaymentLog::CURRENCY_KZT,
            "code"         => \Yii::$app->security->generateRandomString(),
            "created_time" => date("Y-m-d H:i:s"),
            'value'        => 130
        ];
        expect("model is saved", $this->_payment->save())->true();

        // generate arguments for payment via WalleOne
        $fields = WalletOne::generateFields($this->_payment->value, $this->_payment->currency, $this->_payment->code, $this->_payment->description, $this->_payment->company->name);

        // unset signature and check params validation
        unset($fields["WMI_SIGNATURE"]);
        $this->specify("check exception throwing if param is missing", function() use ($fields) {
            $_POST = $fields;
            WalletOne::checkRequest($fields);
        }, ['throws' => 'Exception']);

        // add fields as arguments from server
        $fields["WMI_ORDER_STATE"] = "ACCEPTED";
        // rewrite signature
        $fields["WMI_SIGNATURE"] = WalletOne::generateSignature($fields);
        $_POST = $fields;

        $oldBalance = $this->_payment->company->balance;
        expect("Successfull request validation", WalletOne::checkRequest())->equals($this->_payment->code);
        expect("Successfull confirmation", $this->_payment->setConfirmed())->true();

        $this->tester->canSeeRecord(CompanyPaymentLog::className(), ['value' => $this->_payment->value]);
        $this->tester->canSeeRecord(Company::className(), [
            "id"      => $this->_payment->company->id,
            "balance" => $oldBalance + $this->_payment->value
        ]);

        $_POST = [];
    }
}