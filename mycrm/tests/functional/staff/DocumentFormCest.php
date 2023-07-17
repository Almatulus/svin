<?php

namespace api\tests\staff;

use core\models\company\CompanyPosition;
use core\models\division\Division;
use core\models\Staff;
use FunctionalTester;
use core\models\document\DocumentForm;

class DocumentFormCest
{
    private $responseFormat = [
        'id'              => 'integer',
        'name'            => 'string',
        'has_dental_card' => 'boolean',
        'elements'        => 'array|null'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Staff DocumentForm index');

        $I->sendGET("staff/1/document/form?expand=elements");
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        /** @var Division $notOwnDivision */
        $notOwnDivision = $I->getFactory()->create(Division::class);
        /** @var Staff $notOwnStaff */
        $notOwnStaff = $I->getFactory()->create(Staff::class);
        $notOwnStaff->link('divisions', $notOwnDivision);

        /** @var Division $division */
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        /** @var CompanyPosition $companyPosition */
        $companyPosition = $I->getFactory()->create(CompanyPosition::class, [
            'company_id' => $division->company_id
        ]);
        /** @var Staff $staff */
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $staff->link('companyPositions', $companyPosition);


        /** @var DocumentForm[] $documentForms1 */
        $documentForms1 = $I->getFactory()->seed(3, DocumentForm::class); // Linked to my CompanyPosition
        /** @var DocumentForm[] $documentForms2 */
        $documentForms2 = $I->getFactory()->seed(3, DocumentForm::class);

        foreach ($documentForms1 as $documentForm) {
            $documentForm->link('companyPositions', $companyPosition);
        }


        // Get not my staff DocumentForms
        $I->sendGET("staff/{$notOwnStaff->id}/document/form?expand=elements");
        $I->seeResponseCodeIs(404);


        // Get my staff DocumentForms
        $I->sendGET("staff/{$staff->id}/document/form?expand=elements");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, "$.[*]");

        foreach ($documentForms1 as $documentForm) {
            $I->seeResponseContainsJson(['id' => $documentForm->id]);
        }

        foreach ($documentForms2 as $documentForm) {
            $I->dontSeeResponseContainsJson(['id' => $documentForm->id]);
        }
    }
}
