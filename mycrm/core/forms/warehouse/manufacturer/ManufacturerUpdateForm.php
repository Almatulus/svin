<?php

namespace core\forms\warehouse\manufacturer;

use core\models\warehouse\Manufacturer;

class ManufacturerUpdateForm extends ManufacturerCreateForm
{
    /**
     * ManufacturerUpdateForm constructor.
     * @param Manufacturer $manufacturer
     * @param array $config
     * @internal param int $id
     */
    public function __construct(Manufacturer $manufacturer, array $config = [])
    {
        parent::__construct($config);

        $this->name = $manufacturer->name;
    }
}