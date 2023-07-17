<?php

namespace api\tests\user;

use core\models\company\Company;
use core\models\user\User;
use FunctionalTester;

class CompanyCest
{
    private $user;

    public function _before(FunctionalTester $I)
    {
        $company    = $I->getFactory()->create(Company::class);
        $this->user = $I->getFactory()->create(User::class, [
            'company_id' => $company->id,
        ]);
        $I->assignPermission($this->user, 'companyView');
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests

    /**
     * @TODO Set appropriate user permission to view company info
     */
    public function actionIndex(FunctionalTester $I)
    {
        $I->wantToTest('Company index');

        $I->login($this->user);
        $I->sendGET('user/company');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'id'                   => 'integer',
            'name'                 => 'string',
            'head_name'            => 'string',
            'head_surname'         => 'string|null',
            'head_patronymic'      => 'string|null',
            'status'               => 'integer',
            "status_label"         => "string",
            "logo_id"              => "integer",
            "logo_path"            => "string",
            "category_id"          => "integer|null",
            "category_label"       => "string|null",
            "publish"              => "integer",
            "balance"              => "integer|null",
            "last_payment"         => "date|null",
            "tariff"               => "array",
            "address"              => "string|null",
            "iik"                  => "string|null",
            "bank"                 => "string|null",
            "bin"                  => "string|null",
            "bik"                  => "string|null",
            "phone"                => "string|null",
            "license_issued"       => "date|null",
            "license_number"       => "int|null",
            "widget_prefix"        => "string|null",
            "widget_url"           => "string",
            "file_manager_enabled" => "boolean|null",
            "show_referrer"        => "boolean",
            "show_new_interface"   => "boolean",
            "interval"             => "integer",
            "online_start"         => "time|null",
            "online_finish"        => "time|null",
            'cashback_percent'     => 'int|null'
        ]);

    }
}
