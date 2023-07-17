<?php

namespace api\tests\user;

use FunctionalTester;
use core\models\division\Division;
use core\models\order\OrderDocumentTemplate;
use core\models\Staff;
use core\models\StaffDivisionMap;

class DocumentTemplateCest
{
    private $responseFormat = [
        'id' => 'integer',
        'name' => 'string',
        'filename' => 'string',
        'category_id' => 'integer',
        'company_id' => 'integer|null',
        'path' => 'string|null',
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    /**
     * @group DocumentTemplateCest
     */
    public function index(FunctionalTester $I)
    {
        $I->sendGET('order/document-template');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->seed(10, OrderDocumentTemplate::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendGET("order/document-template");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET('order/document-template/1');
        $I->seeResponseCodeIs(401);

        $I->login();

        $template = $I->getFactory()->create(OrderDocumentTemplate::class);

        $I->sendGET("order/document-template/{$template->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
