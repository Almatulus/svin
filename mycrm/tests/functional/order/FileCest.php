<?php

namespace api\tests\order;

use core\helpers\order\OrderConstants;
use core\models\company\Company;
use core\models\division\Division;
use core\models\File;
use core\models\order\Order;
use core\models\user\User;
use FunctionalTester;

class FileCest
{
    private $responseFormat = [
        'id' => 'integer',
        'path' => 'string',
        'name' => 'string',
        'extension' => 'string',
        'created_at' => 'string:datetime',
    ];

    private $company;
    private $user;
    private $division;

    /** @var Order */
    private $myOrder;

    /** @var Order */
    private $otherOrder;

    public function _before(FunctionalTester $I)
    {

    }

    public function _after(FunctionalTester $I)
    {

    }

    private function init(FunctionalTester $I, $file_manager_enabled)
    {
        $this->company = $I->getFactory()->create(Company::class, [
            'file_manager_enabled' => $file_manager_enabled
        ]);
        $this->user = $I->getFactory()->create(User::class, [
            'company_id' => $this->company->id,
        ]);
        $this->division = $I->getFactory()->create(Division::class, [
            'company_id' => $this->company->id,
        ]);
        $this->myOrder = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'=> OrderConstants::STATUS_ENABLED,
        ]);
        $this->otherOrder = $I->getFactory()->create(Order::class, [
            'status'=> OrderConstants::STATUS_ENABLED,
        ]);
    }

    public function uploadFileManagerFalse(FunctionalTester $I)
    {
        $I->wantToTest('Order File upload (disabled file_manager)');
        $this->init($I, false); // Mandatory

        $I->sendPOST("order/{$this->myOrder->id}/file");
        $I->seeResponseCodeIs(401);

        $I->login($this->user);

        // ***********
        $I->sendPOST("order/{$this->myOrder->id}/file");
        $I->seeResponseCodeIs(403);

        // ***********
        $I->sendPOST("order/{$this->otherOrder->id}/file");
        $I->seeResponseCodeIs(403);
    }

    public function uploadFileManagerTrue(FunctionalTester $I)
    {
        $I->wantToTest('Order File upload (enabled file_manager)');
        $this->init($I, true); // Mandatory

        $I->sendPOST("order/{$this->myOrder->id}/file");
        $I->seeResponseCodeIs(401);

        $I->login($this->user);

        // ***********
        $I->sendPOST("order/{$this->myOrder->id}/file", [], [
            'file' => codecept_data_dir('dummy_file.txt')
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);

        // ***********
        // I Should not be able to upload files for Other Orders
        $I->sendPOST("order/{$this->otherOrder->id}/file");
        $I->seeResponseCodeIs(403);
    }

    public function deleteFileManagerFalse(FunctionalTester $I)
    {
        $I->wantToTest('Order File delete (disabled file_manager)');
        $this->init($I, false); // Mandatory

        /** @var File $myFile */
        $myFile = $I->getFactory()->create(File::class);
        /** @var File $otherFile */
        $otherFile = $I->getFactory()->create(File::class);

        $this->myOrder->link('files', $myFile);
        $this->otherOrder->link('files', $otherFile);

        $I->sendDELETE("order/{$myFile->id}/file");
        $I->seeResponseCodeIs(401);

        $I->login($this->user);

        // ************
        // TODO fake order_id, should be fixed in controller maybe?
        $I->sendDELETE("order/9999999/file/{$myFile->id}");
        $I->seeResponseCodeIs(403);

        // ************
        // TODO fake order_id, should be fixed in controller maybe?
        $I->sendDELETE("order/9999999/file/{$otherFile->id}");
        $I->seeResponseCodeIs(403);
    }

    public function deleteFileManagerTrue(FunctionalTester $I)
    {
        $I->wantToTest('Order File delete (enabled file_manager)');
        $this->init($I, true); // Mandatory

        /** @var File $myFile */
        $myFile = $I->getFactory()->create(File::class);
        /** @var File $otherFile */
        $otherFile = $I->getFactory()->create(File::class);

        $this->myOrder->link('files', $myFile);
        $this->otherOrder->link('files', $otherFile);

        $I->sendDELETE("order/{$myFile->id}/file");
        $I->seeResponseCodeIs(401);

        $I->login($this->user);

        // ************
        // TODO fake order_id, should be fixed in controller maybe?
        $I->sendDELETE("order/9999999/file/{$myFile->id}");
        $I->seeResponseCodeIs(200);

        // ************
        // I Should not be able to delete files for Other Orders
        // TODO fake order_id, should be fixed in controller maybe?
        $I->sendDELETE("order/9999999/file/{$otherFile->id}");
        $I->seeResponseCodeIs(403);
    }
}