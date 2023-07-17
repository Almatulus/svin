<?php

namespace api\tests\user;

use core\helpers\OrderHelper;
use core\models\City;
use core\models\company\Company;
use core\models\division\Division;
use core\models\Payment;
use core\models\ServiceCategory;
use FunctionalTester;

class DivisionCest
{
    private $division;
    private $company;
    private $responseFormat
        = [
            'id'                        => 'integer',
            'address'                   => 'string',
            'category_id'               => 'integer|null',
            'city_id'                   => 'integer',
            'city_name'                 => 'string',
            'country_id'                => 'integer',
            'country_name'              => 'string',
            'company_id'                => 'integer',
            'description'               => 'string|null',
            'default_notification_time' => 'integer',
            'key'                       => 'string|null',
            'latitude'                  => 'float|integer',
            'longitude'                 => 'float|integer',
            'name'                      => 'string|null',
            'phone'                     => 'string|null',
            'rating'                    => 'integer|float',
            'status'                    => 'integer',
            'status_name'               => 'string',
            'status_list'               => 'array',
            'payments'                  => 'array',
            'url'                       => 'string|null',
            'logo_path'                 => 'string|null',
        ];

    public function _before(FunctionalTester $I)
    {
        $this->company  = $I->getFactory()->create(Company::class);
        $this->division = $I->getFactory()->create(Division::class, [
            'company_id' => $this->company->id,
        ]);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Division index');

        $I->login(['company_id' => $this->company->id]);
        $I->sendGET('user/division?expand=payments');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Division view');
        $I->sendGET('user/division/1');
        $I->seeResponseCodeIs(401);

        $I->login(['company_id' => $this->company->id]);

        $another_company = $I->getFactory()->create(Company::class);
        $another_divisions = $I->getFactory()->seed(10, Division::class, [
            'company_id' => $another_company->id
        ]);

        $I->sendGET("user/division/{$another_divisions[0]->id}");
        $I->seeResponseCodeIs(403);

        $I->sendGET("user/division/{$this->division->id}?expand=payments");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Division creation');

        $I->amGoingTo('Create division without valid credentials');
        $I->sendPOST('user/division');
        $I->seeResponseCodeIs(401);

        $I->login(['company_id' => $this->company->id]);

        $I->amGoingTo('Create division with valid credentials and invalid data');
        $I->sendPOST('user/division', ["name" => "",]);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Create division with valid credentials and valid data');
        $category = $I->getFactory()->create(ServiceCategory::class, ['company_id' => $this->company->id]);
        $city = $I->getFactory()->create(City::class);
        $payments = $I->getFactory()->seed(2, Payment::class);

        $I->sendPOST('user/division?expand=payments,phones,settings', [
            'address'                        => $I->getFaker()->address,
            'category_id'                    => $category->id,
            'company_id'                     => $this->company->id,
            'city_id'                        => $city->id,
            'description'                    => $I->getFaker()->text(14),
            'latitude'                       => $I->getFaker()->latitude,
            'longitude'                      => $I->getFaker()->longitude,
            'name'                           => $I->getFaker()->name,
            'status'                         => Division::STATUS_ENABLED,
            'url'                            => $I->getFaker()->url,
            'working_finish'                 => '20:00',
            'working_start'                  => '10:00',
            'default_notification_time'      => $I->getFaker()->randomKey(OrderHelper::getNotificationTimeList()),
            'payments'                       => [$payments[0]->id, $payments[1]->id],
            'phones'                         => [
                $I->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
                $I->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}")
            ],
            'notification_time_before_lunch' => '19:00',
            'notification_time_after_lunch'  => '12:00',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(array_merge($this->responseFormat, [
            'payments' => 'array',
            'phones'   => 'array',
            'settings' => 'array'
        ]));
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Division update');

        $notOwnedDivision = $I->getFactory()->create(Division::class);

        $I->amGoingTo('Update division with invalid credentials');
        $I->sendPUT("user/division/{$notOwnedDivision->id}");
        $I->seeResponseCodeIs(401);

        $I->login(['company_id' => $this->company->id]);

        $I->amGoingTo("Update division without access to resource");
        $I->sendPUT("user/division/{$notOwnedDivision->id}");
        $I->seeResponseCodeIs(403);

        $I->amGoingTo("Update division with valid credentials and invalid data");
        $I->sendPUT("user/division/{$this->division->id}", ['name' => '']);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo("Update division with valid credentials, valid data and with access to resource");

        $category = $I->getFactory()->create(ServiceCategory::class, ['company_id' => $this->company->id]);
        $city = $I->getFactory()->create(City::class);
        $payments = $I->getFactory()->seed(2, Payment::class);

        $I->sendPUT("user/division/{$this->division->id}?expand=payments,phones,settings", [
            'address'                   => $I->getFaker()->address,
            'category_id'               => $category->id,
            'company_id'                => $this->company->id,
            'city_id'                   => $city->id,
            'description'               => $I->getFaker()->text(14),
            'latitude'                  => $I->getFaker()->latitude,
            'longitude'                 => $I->getFaker()->longitude,
            'name'                      => $I->getFaker()->name,
            'status'                    => Division::STATUS_ENABLED,
            'url'                       => $I->getFaker()->url,
            'working_finish'            => '20:00',
            'working_start'             => '10:00',
            'default_notification_time' => $I->getFaker()->randomKey(OrderHelper::getNotificationTimeList()),
            'payments'                  => [$payments[0]->id, $payments[1]->id],
            'phones'                    => [
                $I->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
                $I->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}")
            ],
            'notification_time_before_lunch' => '19:00',
            'notification_time_after_lunch' => '12:00',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(array_merge($this->responseFormat, [
            'payments' => 'array',
            'phones'   => 'array',
            'settings' => 'array'
        ]));
    }
}
