<?php

namespace core\forms\division;

use core\models\division\DivisionService;

class ServiceUpdateForm extends ServiceCreateForm
{
    /**
     * @var DivisionService
     */
    private $service;

    /**
     * ServiceUpdateForm constructor.
     * @param int $id
     * @param array $config
     */
    public function __construct(int $id, array $config = [])
    {
        $this->service = DivisionService::findOne($id);

        parent::__construct($config);
    }

    /**
     *
     */
    public function init()
    {
        parent::init();

        if (!$this->service) {
            throw new \InvalidArgumentException();
        }

        $this->attributes = $this->service->attributes;
        $this->category_ids = $this->service->getCategories()->select('id')->column();
        $this->division_ids = $this->service->getDivisions()->select('id')->column();
        $this->staff = $this->service->getStaffs()->select('id')->column();
    }
}
