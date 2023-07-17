<?php

namespace core\services\warehouse;

use core\models\warehouse\Manufacturer;
use core\repositories\warehouse\ManufacturerRepository;
use core\services\TransactionManager;

class ManufacturerService
{
    /**
     * @var ManufacturerRepository
     */
    private $manufacturers;

    /**
     * @var TransactionManager
     */
    protected $transactionManager;

    public function __construct(
        ManufacturerRepository $manufacturerRepository,
        TransactionManager $transactionManager
    )
    {
        $this->manufacturers = $manufacturerRepository;
        $this->transactionManager = $transactionManager;
    }


    public function create($name, $company_id)
    {
        $manufacturer = Manufacturer::create($name, $company_id);

        $this->transactionManager->execute(function () use ($manufacturer) {
            $this->manufacturers->add($manufacturer);
        });

        return $manufacturer;
    }

    public function edit($id, $name)
    {
        $manufacturer = $this->manufacturers->find($id);
        $manufacturer->edit($name);

        $this->transactionManager->execute(function () use ($manufacturer) {
            $this->manufacturers->edit($manufacturer);
        });

        return $manufacturer;
    }

}
