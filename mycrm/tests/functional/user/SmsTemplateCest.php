<?php

namespace api\tests\user;

use FunctionalTester;
use core\helpers\customer\RequestTemplateHelper;
use core\models\company\Company;
use core\models\customer\CustomerRequestTemplate;

class SmsTemplateCest
{
    private $responseFormat = [
        'id'            => 'integer|null',
        'key'           => 'string',
        "label"         => "string|null",
        'template'      => 'string',
        'is_enabled'    => 'boolean',
        'is_delayed'    => 'boolean',
        'quantity'      => 'integer|null',
        'quantity_type' => 'integer|null'
    ];

    /**
     * @var Company
     */
    private $company;

    public function _before(FunctionalTester $I)
    {
        $this->company = $I->getFactory()->create(Company::class);
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Fetching sms template');

        $I->amGoingTo('Fetch sms template without authorization');
        $I->sendGET('user/sms-template');
        $I->seeResponseCodeIs(401);

        $user = $I->login(['company_id' => $this->company->id]);
        $I->getFactory()->create(CustomerRequestTemplate::class, ['company_id' => $user->company_id]);
        $I->amGoingTo('Fetch sms template');
        $I->sendGET('user/sms-template');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Update sms templates');

        $I->amGoingTo('Update sms templates without authorization');
        $I->sendPUT('user/sms-template');
        $I->seeResponseCodeIs(401);

        /** @var CustomerRequestTemplate[] $templates */
        $templates = CustomerRequestTemplate::loadTemplates($this->company->id);
        $validData = [];
        $invalidData = [];
        foreach ($templates as $template) {
            $validData[$template->key] = [
                'is_enabled'    => true,
                'quantity'      => $template->isDelayedByDefault() ? 5 : null,
                'quantity_type' => $template->isDelayedByDefault() ? RequestTemplateHelper::QUANTITY_TYPE_DAYS : null,
                'template'      => empty($template->template) ? $I->getFaker()->text('12') : $template->template
            ];
            $invalidData[$template->key] = [
                'is_enabled'    => true,
                'quantity'      => $template->isDelayedByDefault() ? 5 : null,
                'quantity_type' => $template->isDelayedByDefault() ? RequestTemplateHelper::QUANTITY_TYPE_DAYS : null,
                'template'      => ""
            ];
        }

        $I->login(['company_id' => $this->company->id]);

        $I->amGoingTo('Update sms templates with invalid data');
        $I->sendPUT('user/sms-template', $invalidData);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Update sms templates');
        $I->sendPUT('user/sms-template', $validData);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

}
