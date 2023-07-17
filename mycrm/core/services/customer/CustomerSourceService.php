<?php

namespace core\services\customer;

use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerSource;
use core\repositories\customer\CustomerSourceRepository;
use core\services\TransactionManager;

class CustomerSourceService
{
    private $transactionManager;
    /**
     * @var CustomerSourceRepository
     */
    private $customerSources;

    public function __construct(
        CustomerSourceRepository $customerSources,
        TransactionManager $transactionManager
    ) {
        $this->transactionManager = $transactionManager;
        $this->customerSources    = $customerSources;
    }

    /**
     * @param int $source_id
     * @param int $destination_id
     * @param int $company_id
     *
     * @return int
     */
    public function moveCustomers(
        int $source_id,
        int $destination_id,
        int $company_id
    ) {
        if ($source_id === $destination_id) {
            throw new \DomainException('Moving to the same CustomerSource model');
        }

        $source = $this->customerSources->find($source_id);
        if ($source->company_id !== $company_id) {
            throw new \DomainException('Wrong company source');
        }

        $destination = $this->customerSources->find($destination_id);
        if ($source->company_id !== $company_id) {
            throw new \DomainException('Wrong company source');
        }

        return CompanyCustomer::updateAll(
            ['source_id' => $destination->id],
            ['source_id' => $source->id, 'company_id' => $company_id]
        );
    }

    /**
     * @param string $name
     * @param int $company_id
     * @return CustomerSource
     */
    public function create(string $name, int $company_id)
    {
        $model = CustomerSource::add($name, $company_id);

        $this->transactionManager->execute(function () use ($model) {
            $this->customerSources->add($model);
        });

        return $model;
    }

    /**
     * @param int $id
     * @param string $name
     * @return CustomerSource
     */
    public function update(int $id, string $name)
    {
        $model = $this->customerSources->find($id);
        $model->name = $name;

        $this->transactionManager->execute(function () use ($model) {
            $this->customerSources->edit($model);
        });

        return $model;
    }
}