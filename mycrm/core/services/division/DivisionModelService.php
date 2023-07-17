<?php

namespace core\services\division;

use core\helpers\division\DivisionHelper;
use core\models\division\Division;
use core\models\division\DivisionPayment;
use core\models\division\DivisionPhone;
use core\models\division\DivisionSettings;
use core\models\finance\CompanyCash;
use core\models\ServiceCategory;
use core\repositories\CompanyCashRepository;
use core\repositories\division\DivisionPaymentRepository;
use core\repositories\division\DivisionPhoneRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\exceptions\NotFoundException;
use core\repositories\ServiceCategoryRepository;
use core\services\division\dto\DivisionData;
use core\services\division\dto\DivisionSettingsData;
use core\services\TransactionManager;

class DivisionModelService
{
    private $companyCashRepository;
    private $divisionRepository;
    private $divisionPaymentRepository;
    private $divisionPhoneRepository;
    private $serviceCategoryRepository;
    private $transactionManager;

    /**
     * DivisionModelService constructor.
     * @param CompanyCashRepository $companyCashRepository
     * @param DivisionRepository $divisionRepository
     * @param DivisionPaymentRepository $divisionPaymentRepository
     * @param DivisionPhoneRepository $divisionPhoneRepository
     * @param ServiceCategoryRepository $serviceCategoryRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        CompanyCashRepository $companyCashRepository,
        DivisionRepository $divisionRepository,
        DivisionPaymentRepository $divisionPaymentRepository,
        DivisionPhoneRepository $divisionPhoneRepository,
        ServiceCategoryRepository $serviceCategoryRepository,
        TransactionManager $transactionManager
    ) {
        $this->companyCashRepository = $companyCashRepository;
        $this->divisionRepository = $divisionRepository;
        $this->divisionPaymentRepository = $divisionPaymentRepository;
        $this->divisionPhoneRepository = $divisionPhoneRepository;
        $this->serviceCategoryRepository = $serviceCategoryRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param DivisionData         $divisionData
     * @param                      $payments
     * @param                      $phones
     * @param DivisionSettingsData $settingsData
     *
     * @return Division
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function create(DivisionData $divisionData, $payments, $phones, DivisionSettingsData $settingsData)
    {
        $division = Division::add(
            $divisionData->address,
            $divisionData->category_id,
            $divisionData->company_id,
            $divisionData->city_id,
            $divisionData->description,
            $divisionData->latitude,
            $divisionData->longitude,
            $divisionData->name,
            $divisionData->status,
            $divisionData->url,
            $divisionData->working_finish,
            $divisionData->working_start,
            $divisionData->default_notification_time
        );

        $settings = $this->getSettings($division, $settingsData);

        if (!empty($divisionData->logo_id)) {
            $division->changeLogo($divisionData->logo_id);
        }

        $companyCash = CompanyCash::add(
            $division,
            \Yii::t('app', 'Company Cash') . " - " . $division->name,
            CompanyCash::TYPE_CASH_BOX,
            0,
            null,
            false
        );

        $this->transactionManager->execute(function () use ($companyCash, $division, $payments, $phones, $settings) {
            $this->divisionRepository->add($division);
            $this->companyCashRepository->add($companyCash);
            $this->savePayments($payments, $division);
            $this->savePhones($phones, $division);
            $this->setDefaultCategories($division);
            $this->saveSettings($division, $settings);
        });

        return $division;
    }

    /**
     * @param              $id
     * @param DivisionData $divisionData
     * @param              $payments
     * @param              $phones
     *
     * @return Division
     * @throws \Exception
     */
    public function edit($id, DivisionData $divisionData, $payments, $phones, DivisionSettingsData $settingsData)
    {
        $division = $this->divisionRepository->find($id);

        $division->edit(
            $divisionData->address,
            $divisionData->category_id,
            $divisionData->company_id,
            $divisionData->city_id,
            $divisionData->description,
            $divisionData->latitude,
            $divisionData->longitude,
            $divisionData->name,
            $divisionData->status,
            $divisionData->url,
            $divisionData->working_finish,
            $divisionData->working_start,
            $divisionData->default_notification_time
        );

        $settings = $this->getSettings($division, $settingsData);

        if (!empty($divisionData->logo_id)) {
            $division->changeLogo($divisionData->logo_id);
        }

        $this->transactionManager->execute(function () use (
            $division,
            $payments,
            $phones,
            $settings
        ) {
            $this->divisionRepository->edit($division);
            $this->savePayments($payments, $division);
            $this->savePhones($phones, $division);
            $this->saveSettings($division, $settings);
        });

        return $division;
    }

    /**
     * @param $payments
     * @param Division $division
     */
    private function savePayments($payments, Division $division)
    {
        $this->divisionPaymentRepository->deletePayments($division->id);
        foreach ($payments as $key => $payment_id) {
            try {
                $payment = $this->divisionPaymentRepository->findByPayment($division->id, $payment_id);
                $payment->enable();
                $this->divisionPaymentRepository->edit($payment);
            } catch (NotFoundException $e) {
                $payment = DivisionPayment::add($division->id, $payment_id);
                $this->divisionPaymentRepository->add($payment);
            }
        }
    }

    /**
     * @param $phones
     * @param Division $division
     */
    private function savePhones($phones, Division $division)
    {
        $this->divisionPhoneRepository->deletePhones($division->id);
        foreach ($phones as $key => $value) {
            if ($value && $value !== '') {
                try {
                    $phone = $this->divisionPhoneRepository->findByPhone($division->id, $value);
                    $phone->enable();
                    $this->divisionPhoneRepository->edit($phone);
                } catch (NotFoundException $e) {
                    $phone = DivisionPhone::add($division->id, $value);
                    $this->divisionPhoneRepository->add($phone);
                }
            }
        }
    }

    /**
     * Set default categories for division
     * @param Division $division
     */
    private function setDefaultCategories(Division $division)
    {
        $defaultCategories = DivisionHelper::getDefaultCategories();
        if (isset($defaultCategories[$division->category_id])) {
            $categories = $defaultCategories[$division->category_id];
            foreach ($categories as $key => $categoryName) {
                try {
                    $this->serviceCategoryRepository->findByCompanyAndName($division->company_id, $categoryName);
                } catch (NotFoundException $e) {
                    $category = new ServiceCategory([
                        'company_id'         => $division->company_id,
                        'name'               => $categoryName,
                        'parent_category_id' => $division->category_id,
                        'type'               => ServiceCategory::TYPE_CATEGORY_DYNAMIC
                    ]);
                    $this->serviceCategoryRepository->add($category);
                }
            }
        }
    }

    /**
     * @param Division $division
     * @param DivisionSettingsData $settingsData
     * @return DivisionSettings
     */
    protected function getSettings(Division $division, DivisionSettingsData $settingsData)
    {
        if (!($settings = $division->settings)) {
            $settings = DivisionSettings::add(
                $settingsData->getNotificationTimeBeforeDelimiter(),
                $settingsData->getNotificationTimeAfterDelimiter()
            );
        } else {
            $settings->edit(
                $settingsData->getNotificationTimeBeforeDelimiter(),
                $settingsData->getNotificationTimeAfterDelimiter()
            );
        }

        return $settings;
    }

    /**
     * @param Division $division
     * @param DivisionSettings $settings
     */
    protected function saveSettings(Division $division, DivisionSettings $settings)
    {
        $settings->division_id = $division->id;
        $this->companyCashRepository->save($settings);
        $division->populateRelation('settings', $settings);
    }
}
