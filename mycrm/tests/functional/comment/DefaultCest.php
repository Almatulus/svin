<?php

namespace api\tests\comment;

use core\models\Country;
use core\models\medCard\MedCardComment;
use core\models\medCard\MedCardCompanyComment;
use FunctionalTester;

class DefaultCest
{
    private $responseFormat = [
        'category_id' => 'integer',
        'comment' => 'string',
//        'company_id' => 'integer', // Optional, endpoint return array of "MedCartComments" and "MedCartCompanyComment"
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Comment index');

        $I->sendGET('comment');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        /** @var MedCardComment $medCardComment */
        $medCardComment = $I->getFactory()->create(MedCardComment::class, [
            'comment' => 'global comment',
        ]);
        /** @var MedCardCompanyComment $medCardMyCompanyComment */
        $medCardMyCompanyComment = $I->getFactory()->create(MedCardCompanyComment::className(), [
            'comment' => 'my company comment',
            'company_id' => $user->company_id,
        ]);
        /** @var MedCardCompanyComment $medCardOtherCompanyComment */
        $medCardOtherCompanyComment = $I->getFactory()->create(MedCardCompanyComment::className(), [
            'comment' => 'other company comment',
        ]);

        $I->sendGET('comment');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        $I->seeResponseContainsJson(['comment' => $medCardComment->comment]);
        $I->seeResponseContainsJson(['comment' => $medCardMyCompanyComment->comment]);
        $I->dontSeeResponseContainsJson(['comment' => $medCardOtherCompanyComment->comment]);
    }
}
