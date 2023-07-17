<?php

namespace core\services\company;

use core\models\company\Insurance;
use core\repositories\company\InsuranceRepository;
use core\repositories\exceptions\NotFoundException;
use core\services\TransactionManager;

/**
 * @var InsuranceRepository insuranceRepository
 */
class InsuranceService
{
    private $transactionManager;
    private $insuranceRepository;

    public function __construct(
        InsuranceRepository $insuranceRepository,
        TransactionManager $transactionManager
    ) {
        $this->insuranceRepository = $insuranceRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param int $company_id
     * @param int $insurance_company_id
     *
     * @return Insurance
     * @throws \Exception
     */
    public function createCompanyInsurance(int $company_id, int $insurance_company_id): Insurance
    {
        try {
            $companyInsurance = $this->insuranceRepository->findByInsuranceCompany($company_id, $insurance_company_id);
            $companyInsurance->deleted_time = null;
        } catch (NotFoundException $e) {
            $companyInsurance = new Insurance([
                'company_id'           => $company_id,
                'insurance_company_id' => $insurance_company_id
            ]);
        }

        $this->transactionManager->execute(function () use ($companyInsurance) {
            if ($companyInsurance->isNewRecord) {
                $this->insuranceRepository->add($companyInsurance);
            } else {
                $this->insuranceRepository->edit($companyInsurance);
            }
        });

        return $companyInsurance;
    }

    /**
     * @param int $company_id
     * @param int $insurance_company_id
     *
     * @throws \Exception
     */
    public function remove(int $company_id, int $insurance_company_id)
    {
        $model = $this->insuranceRepository->findByInsuranceCompany($company_id, $insurance_company_id);

        $model->remove();

        $this->transactionManager->execute(function () use ($model) {
            $this->insuranceRepository->edit($model);
        });
    }

    /**
     * @param int   $company_id
     * @param array $insurance_companies
     *
     * @throws \Exception
     */
    public function mapInsuranceCompanies(int $company_id, array $insurance_companies)
    {
        $oldInsuranceCompanyIds = Insurance::find()->where([
            'company_id'   => $company_id,
            'deleted_time' => null
        ])->select('insurance_company_id')->column();

        $deletedIds = array_diff($oldInsuranceCompanyIds, $insurance_companies);

        $this->transactionManager->execute(function () use ($company_id, $insurance_companies, $deletedIds) {
            foreach ($insurance_companies as $insurance_company_id) {
                $this->createCompanyInsurance(
                    $company_id,
                    $insurance_company_id
                );
            }

            foreach ($deletedIds as $insurance_company_id) {
                // ToDo temporary condition, in case if insurance was not mapped with insurance company
                if ($insurance_company_id) {
                    $this->remove(
                        $company_id,
                        $insurance_company_id
                    );
                }
            }
        });
    }
}
