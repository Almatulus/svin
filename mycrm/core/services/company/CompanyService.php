<?php

namespace core\services\company;

use core\helpers\finance\CompanyCostItemHelper;
use core\models\company\Company;
use core\models\company\CompanyPosition;
use core\models\company\TariffPayment;
use core\models\CompanyPaymentLog;
use core\models\finance\CompanyCostItem;
use core\models\Position;
use core\models\webcall\WebCall;
use core\repositories\BaseRepository;
use core\repositories\company\CompanyPositionRepository;
use core\repositories\company\CompanyRepository;
use core\repositories\CompanyCashRepository;
use core\repositories\CompanyCostItemRepository;
use core\repositories\document\DocumentFormRepository;
use core\repositories\user\UserRepository;
use core\repositories\WebcallRepository;
use core\services\dto\CompanyDetailsData;
use core\services\dto\CompanyPaymentData;
use core\services\dto\PersonData;
use core\services\TransactionManager;
use yii\helpers\ArrayHelper;

class CompanyService
{
    private $baseRepository;
    private $companyRepository;
    private $webcallRepository;
    private $companyCashRepository;
    private $companyCostItemRepository;
    private $documentFormRepository;
    private $companyPositionRepository;
    private $transactionManager;
    private $users;

    public function __construct(
        BaseRepository $baseRepository,
        CompanyRepository $companyRepository,
        CompanyCashRepository $companyCashRepository,
        WebcallRepository $webcallRepository,
        CompanyCostItemRepository $companyCostItemRepository,
        DocumentFormRepository $documentFormRepository,
        CompanyPositionRepository $companyPositionRepository,
        UserRepository $users,
        TransactionManager $transactionManager
    ) {
        $this->baseRepository            = $baseRepository;
        $this->companyRepository         = $companyRepository;
        $this->companyCashRepository     = $companyCashRepository;
        $this->transactionManager        = $transactionManager;
        $this->webcallRepository         = $webcallRepository;
        $this->documentFormRepository    = $documentFormRepository;
        $this->companyPositionRepository = $companyPositionRepository;
        $this->companyCostItemRepository = $companyCostItemRepository;
        $this->users                     = $users;
    }

    /**
     * @param integer            $status
     * @param boolean            $publish
     * @param integer            $category_id
     * @param boolean            $web_call_access
     * @param boolean            $file_manager_enabled
     * @param boolean            $show_referrer
     * @param integer            $interval
     * @param boolean            $show_new_interface
     * @param CompanyDetailsData $companyDetailsData
     * @param PersonData         $personData
     * @param CompanyPaymentData $companyPaymentData
     * @param boolean            $unlimited_sms
     * @param boolean            $notify_about_order
     *
     * @param int|null           $cashback_percent
     * @param bool               $limit_auth_time_by_schedule
     * @param bool               $enable_integration
     *
     * @return Company
     * @throws \Exception
     */
    public function add(
        $status,
        $publish,
        $category_id,
        $web_call_access,
        $file_manager_enabled,
        $show_referrer,
        $interval,
        $show_new_interface,
        CompanyDetailsData $companyDetailsData,
        PersonData $personData,
        CompanyPaymentData $companyPaymentData,
        $unlimited_sms,
        $notify_about_order,
        int $cashback_percent = null,
        $limit_auth_time_by_schedule = false,
        $enable_integration = false
    ) {
        $company = Company::add(
            $companyDetailsData->name,
            $status,
            $publish,
            $category_id,
            $personData->name,
            $personData->surname,
            $personData->patronymic,
            $companyPaymentData->tariff_id,
            $companyDetailsData->address,
            $companyDetailsData->bank,
            $companyDetailsData->bik,
            $companyDetailsData->bin,
            $companyDetailsData->iik,
            $companyDetailsData->license_issued,
            $companyDetailsData->license_number,
            $companyDetailsData->phone,
            $file_manager_enabled,
            $show_referrer,
            $interval,
            $unlimited_sms,
            $notify_about_order,
            $limit_auth_time_by_schedule,
            $enable_integration
        );
        $company->cashback_percent = $cashback_percent;
        $company->setNewInterface($show_new_interface);
        $webCall   = WebCall::add($company, $web_call_access);
        $costItems = array_map(function ($item) use ($company) {
            return CompanyCostItem::add(
                $company, 
                $item['name'], 
                $item['type'],
                null, 
                $item['cost_item_type'], 
                false,
                null
            );
        }, CompanyCostItemHelper::getInitialItems());

        $companyPositions = $this->getCompanyPositions($company);

        $this->transactionManager->execute(function () use (
            $company,
            $webCall,
            $costItems,
            $companyPositions
        ) {
            $this->companyRepository->add($company);
            $this->webcallRepository->save($webCall);
            foreach ($costItems as $costItem) {
                $this->companyCostItemRepository->add($costItem);
            }
            foreach ($companyPositions as $companyPosition) {
                $this->companyPositionRepository->add($companyPosition);
            }
        });

        return $company;
    }

    /**
     * @param Company $company
     * @return CompanyPosition[]
     */
    private function getCompanyPositions(Company $company)
    {
        $companyPositions = [];
        $positions = Position::find()->category($company->category_id)->all();

        foreach ($positions as $position){
            $companyPosition = CompanyPosition::add(
                $position->name,
                $position->description,
                $company,
                $position->id
            );

            $document_form_ids = ArrayHelper::getColumn($position->documentForms, 'id');
            $documentForms = [];
            if ( ! empty($document_form_ids) ) {
                $documentForms = array_map(function ($document_form_id) {
                    return $this->documentFormRepository->find($document_form_id);
                }, $document_form_ids);
            }
            $companyPosition->setDocumentFormsRelation($documentForms);
            $companyPositions[] = $companyPosition;
        }

        return $companyPositions;
    }

    /**
     * @param integer $company_id
     * @param CompanyDetailsData $companyDetailsData
     * @param PersonData $personData
     *
     * @param bool $notify_about_order
     * @param int|null $cashback_percent
     * @return Company
     */
    public function restrictEdit(
        $company_id,
        CompanyDetailsData $companyDetailsData,
        PersonData $personData,
        bool $notify_about_order,
        int $cashback_percent = null
    ) {
        $model = $this->companyRepository->find($company_id);

        $model->rename(
            $companyDetailsData->name,
            $personData->name,
            $personData->surname,
            $personData->patronymic
        );
        $model->editDetails(
            $companyDetailsData->address,
            $companyDetailsData->bank,
            $companyDetailsData->bik,
            $companyDetailsData->bin,
            $companyDetailsData->iik,
            $companyDetailsData->license_issued,
            $companyDetailsData->license_number,
            $companyDetailsData->phone,
            $companyDetailsData->logo_id,
            $companyDetailsData->widget_prefix,
            $companyDetailsData->online_start,
            $companyDetailsData->online_finish
        );
        $model->notify_about_order = $notify_about_order;
        $model->cashback_percent = $cashback_percent;

        $this->companyRepository->edit($model);

        return $model;
    }

    /**
     * @param integer            $company_id
     * @param integer            $status
     * @param boolean            $publish
     * @param integer            $category_id
     * @param boolean            $web_call_access
     * @param boolean            $file_manager_enabled
     * @param boolean            $show_referrer
     * @param integer            $interval
     * @param boolean            $show_new_interface
     * @param CompanyDetailsData $companyDetailsData
     * @param PersonData         $personData
     * @param CompanyPaymentData $companyPaymentData
     * @param boolean            $unlimited_sms
     * @param boolean            $notify_about_order
     *
     * @param int|null           $cashback_percent
     * @param bool               $limit_auth_time_by_schedule
     * @param bool               $enable_integration
     *
     * @return Company
     * @throws \Exception
     * @internal param string $company_name
     */
    public function edit(
        $company_id,
        $status,
        $publish,
        $category_id,
        $web_call_access,
        $file_manager_enabled,
        $show_referrer,
        $interval,
        $show_new_interface,
        CompanyDetailsData $companyDetailsData,
        PersonData $personData,
        CompanyPaymentData $companyPaymentData,
        $unlimited_sms,
        $notify_about_order,
        int $cashback_percent = null,
        $limit_auth_time_by_schedule = false,
        $enable_integration = false
    ) {
        $company = $this->companyRepository->find($company_id);

        $company->edit(
            $companyDetailsData->name,
            $status,
            $publish,
            $category_id,
            $personData->name,
            $personData->surname,
            $personData->patronymic,
            $companyPaymentData->tariff_id,
            $companyDetailsData->address,
            $companyDetailsData->bank,
            $companyDetailsData->bik,
            $companyDetailsData->bin,
            $companyDetailsData->iik,
            $companyDetailsData->license_issued,
            $companyDetailsData->license_number,
            $companyDetailsData->phone,
            $file_manager_enabled,
            $show_referrer,
            $interval,
            $unlimited_sms,
            $notify_about_order,
            $limit_auth_time_by_schedule,
            $enable_integration
        );
        $company->cashback_percent = $cashback_percent;
        $company->setNewInterface($show_new_interface);

        $webcall = $company->setupWebCall($web_call_access);

        $this->transactionManager->execute(function () use (
            $company,
            $webcall
        ) {
            $this->companyRepository->edit($company);
            $this->webcallRepository->save($webcall);

            if (!$webcall->isEnabled()) {
                $webcall->unsubscribe();
            }

            if ( ! $company->isActive()) {
                $this->disableUsers($company);
                $this->logoutCompanyUsers($company->id);
            }
        });

        return $company;
    }

    /**
     * @param $company_id
     * @param $currency
     * @param $description
     * @param $message
     * @param $value
     * @param $is_confirmed
     *
     * @return CompanyPaymentLog
     * @throws \Exception
     */
    public function addPayment(
        $company_id,
        $currency,
        $description,
        $message,
        $value,
        $is_confirmed
    ) {
        $company = $this->companyRepository->find($company_id);

        $paymentLog = CompanyPaymentLog::add(
            $company->id,
            $currency,
            $description,
            $message,
            $value,
            $is_confirmed,
            null
        );

        $this->transactionManager->execute(function () use ($paymentLog) {
            $this->baseRepository->add($paymentLog);
        });

        return $paymentLog;
    }

    /**
     * @param int    $id
     * @param int    $sum
     * @param int    $period
     * @param string $start_date
     *
     * @throws \Exception
     */
    public function payTariff(
        int $id,
        int $sum,
        int $period,
        string $start_date
    ) {
        $company = $this->companyRepository->find($id);

        $this->guardTariffPayment($id, $start_date, $period);

        $tariffPayment = new TariffPayment([
            'sum'        => $sum,
            'company_id' => $company->id,
            'period'     => $period,
            'start_date' => $start_date,
        ]);

        $this->transactionManager->execute(function () use ($tariffPayment) {
            $tariffPayment->save(false);
        });
    }

    /**
     * @param int    $id
     * @param int    $sum
     * @param int    $period
     * @param string $start_date
     *
     * @throws \Exception
     */
    public function editTariffPayment(int $id, int $sum, int $period, string $start_date)
    {
        $tariffPayment = $this->companyRepository->findTariffPayment($id);

        $this->guardTariffPayment($tariffPayment->company_id, $start_date, $period, $tariffPayment->id);

        $tariffPayment->sum = $sum;
        $tariffPayment->period = $period;
        $tariffPayment->start_date = $start_date;

        $this->transactionManager->execute(function () use ($tariffPayment) {
            $tariffPayment->save(false);
        });
    }

    public function logoutCompanyUsers($company_id)
    {
        $users = $this->users->findAllByCompany($company_id);

        foreach ($users as $user) {
            $user->generateToken();
            $this->users->edit($user);
        }
    }

    /**
     * @param int $company_id
     * @param string $start_date
     * @param int $period
     * @param int $id
     */
    private function guardTariffPayment(int $company_id, string $start_date, int $period, int $id = null)
    {
        $end_date = (new \DateTime($start_date))->modify("+ {$period} months");

        $paymentExists = TariffPayment::find()
            ->where(['company_id' => $company_id])
            ->andWhere([
                "OR",
                "start_date <= :start_date AND :start_date < start_date + period * INTERVAL '1 MONTH'",
                "start_date < :end_date AND :end_date <= start_date + period * INTERVAL '1 MONTH'"
            ])
            ->andFilterWhere(['<>', 'id', $id])
            ->params([':start_date' => $start_date, ':end_date' => $end_date->format("Y-m-d")])
            ->exists();

        if ($paymentExists) {
            throw new \DomainException("За данный период существует оплата.");
        }
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     */
    public function groupCategories(int $id)
    {
        $company = $this->companyRepository->find($id);

        if ($company->category_id != \core\models\ServiceCategory::ROOT_BEAUTY) {
            throw new \DomainException("Not beauty company");
        }

        $categories = \core\models\ServiceCategory::find()->byCompanyId($company->id)->all();

        $this->transactionManager->execute(function () use ($categories) {
            foreach ($categories as $category) {
                $staticCategory = \core\models\ServiceCategory::find()->staticType()->byName($category->name)->one();

                if ($staticCategory) {
                    $category->updateAttributes(['parent_category_id' => $staticCategory->parent_category_id]);
                }
            }
        });
    }

    /**
     * @param Company $company
     */
    private function disableUsers(Company $company)
    {
        foreach ($company->users as $user) {
            $user->disable();
        }
    }
}
