<?php

namespace tests\services\medCard;

use core\helpers\medCard\MedCardToothHelper;
use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\medCard\MedCardCommentCategory;
use core\models\medCard\MedCardCompanyComment;
use core\models\medCard\MedCardDiagnosis;
use core\models\medCard\MedCardTab;
use core\models\medCard\MedCardTabComment;
use core\models\medCard\MedCardTabService;
use core\models\medCard\MedCardTooth;
use core\models\medCard\MedCardToothDiagnosis;
use core\models\order\Order;
use core\services\medCard\dto\MedCardTabCommentData;
use core\services\medCard\MedCardModelService;
use core\services\order\dto\OrderServiceData;
use core\services\order\dto\ToothData;

class MedCardModelServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var MedCardModelService
     */
    private $service;

    /**
     * @var Company
     */
    private $company;

    /**
     * @throws \Exception
     */
    public function testCreateTab()
    {
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $this->company->id
        ]);
        $division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->company->id
        ]);
        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'division_id' => $division->id
        ]);

        $toothDiagnosis = $this->tester->getFactory()->create(MedCardToothDiagnosis::class, [
            'company_id' => $this->company->id,
        ]);
        $medCardCommentCategory = $this->tester->getFactory()->create(MedCardCommentCategory::class);
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $divisionService->link('divisions', $division);

        $teeth_num       = $this->tester->getFaker()->randomElement(MedCardToothHelper::allTeeth());
        $orderTeethData  = [
            new ToothData(
                $teeth_num,
                MedCardToothHelper::getType(intval($teeth_num)),
                $toothDiagnosis->id,
                $this->tester->getFaker()->randomNumber(1)
            ),
        ];
        $medCardTabCommentsData = [
            new MedCardTabCommentData($this->tester->getFaker()->text, $medCardCommentCategory->id)
        ];
        $servicesData = [
            new OrderServiceData(
                $divisionService->id,
                $divisionService->price,
                null,
                $this->tester->getFaker()->randomNumber(2),
                $this->tester->getFaker()->randomNumber(2)
            )
        ];
        $diagnosis = $this->tester->getFactory()->create(MedCardDiagnosis::class);

        $model = $this->service->createTab(
            $order->created_user_id,
            $this->company->id,
            $order->id,
            $orderTeethData,
            $medCardTabCommentsData,
            $servicesData,
            $diagnosis->id
        );

        verify($model)->isInstanceOf(MedCardTab::class);

        $this->tester->canSeeRecord(MedCardTab::class, [
            'id'           => $model->id,
            'med_card_id'   => $model->med_card_id,
        ]);

        $this->tester->canSeeRecord(MedCardTooth::class, [
            'med_card_tab_id' => $model->id,
            'teeth_num' => $orderTeethData[0]->number,
            'mobility' => $orderTeethData[0]->mobility,
            'teeth_diagnosis_id' => $orderTeethData[0]->diagnosis_id,
        ]);

        $this->tester->canSeeRecord(MedCardTabComment::class, [
            'med_card_tab_id' => $model->id,
            'category_id' => $medCardTabCommentsData[0]->comment_template_category_id,
            'comment' => $medCardTabCommentsData[0]->comment,
        ]);

        $this->tester->canSeeRecord(MedCardTabService::class, [
            'med_card_tab_id' => $model->id,
            'division_service_id' => $servicesData[0]->division_service_id,
            'quantity' => $servicesData[0]->quantity,
            'discount' => $servicesData[0]->discount,
            'price' => $servicesData[0]->price,
        ]);

        $this->tester->canSeeRecord(MedCardCompanyComment::class, [
            'company_id' => $this->company->id,
            'category_id' => $medCardTabCommentsData[0]->comment_template_category_id,
            'comment' => $medCardTabCommentsData[0]->comment,
        ]);
    }

    public function testEditTab()
    {
        /** @var MedCardTab $medCardTab */
        $medCardTab = $this->tester->getFactory()->create(MedCardTab::class);

        $division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->company->id
        ]);

        $toothDiagnosis = $this->tester->getFactory()->create(MedCardToothDiagnosis::class, [
            'company_id' => $this->company->id,
        ]);
        $medCardCommentCategory = $this->tester->getFactory()->create(MedCardCommentCategory::class);
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $divisionService->link('divisions', $division);

        $teeth_num = $this->tester->getFaker()->randomElement(MedCardToothHelper::allTeeth());
        $orderTeethData = [
            new ToothData(
                $teeth_num,
                MedCardToothHelper::getType(intval($teeth_num)),
                $toothDiagnosis->id,
                $this->tester->getFaker()->randomNumber(1)
            ),
        ];
        $medCardTabCommentsData = [
            new MedCardTabCommentData($this->tester->getFaker()->text, $medCardCommentCategory->id)
        ];
        $servicesData = [
            new OrderServiceData(
                $divisionService->id,
                $divisionService->price,
                null,
                $this->tester->getFaker()->randomNumber(2),
                $this->tester->getFaker()->randomNumber(2)
            )
        ];
        $diagnosis = $this->tester->getFactory()->create(MedCardDiagnosis::class);

        $model = $this->service->editTab(
            $medCardTab->medCard->order->created_user_id,
            $this->company->id,
            $medCardTab->id,
            $orderTeethData,
            $medCardTabCommentsData,
            $servicesData,
            $diagnosis->id
        );

        verify($model)->isInstanceOf(MedCardTab::class);

        $this->tester->canSeeRecord(MedCardTooth::class, [
            'med_card_tab_id'    => $medCardTab->id,
            'teeth_num'          => $orderTeethData[0]->number,
            'mobility'           => $orderTeethData[0]->mobility,
            'teeth_diagnosis_id' => $orderTeethData[0]->diagnosis_id,
        ]);

        $this->tester->canSeeRecord(MedCardTabComment::class, [
            'med_card_tab_id' => $medCardTab->id,
            'category_id'     => $medCardTabCommentsData[0]->comment_template_category_id,
            'comment'         => $medCardTabCommentsData[0]->comment,
        ]);

        $this->tester->canSeeRecord(MedCardTabService::class, [
            'med_card_tab_id'     => $medCardTab->id,
            'division_service_id' => $servicesData[0]->division_service_id,
            'quantity'            => $servicesData[0]->quantity,
            'discount'            => $servicesData[0]->discount,
            'price'               => $servicesData[0]->price,
        ]);

        $this->tester->canSeeRecord(MedCardCompanyComment::class, [
            'company_id'  => $this->company->id,
            'category_id' => $medCardTabCommentsData[0]->comment_template_category_id,
            'comment'     => $medCardTabCommentsData[0]->comment,
        ]);
    }

    public function testDeleteTab()
    {
        /** @var MedCardTab $medCardTab */
        $medCardTab = $this->tester->getFactory()->create(MedCardTab::class);

        $this->tester->getFactory()->create(MedCardTabComment::class, [
            'med_card_tab_id' => $medCardTab->id
        ]);
        $this->tester->getFactory()->create(MedCardTooth::class, [
            'med_card_tab_id' => $medCardTab->id
        ]);
        $this->tester->getFactory()->create(MedCardTabService::class, [
            'med_card_tab_id' => $medCardTab->id,
        ]);

        $this->service->deleteTab($medCardTab->id);

        $this->tester->cantSeeRecord(MedCardTab::class, ['id' => $medCardTab->id]);
        $this->tester->cantSeeRecord(MedCardTabComment::class, ['med_card_tab_id' => $medCardTab->id]);
        $this->tester->cantSeeRecord(MedCardTooth::class, ['med_card_tab_id' => $medCardTab->id]);
        $this->tester->cantSeeRecord(MedCardTabService::class, ['med_card_tab_id' => $medCardTab->id]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        $this->company = $this->tester->getFactory()->create(Company::class);
        $this->service = \Yii::createObject(MedCardModelService::class);
    }

    protected function _after()
    {
    }

}