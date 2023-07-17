<?php

namespace core\forms\warehouse\delivery;

use core\models\warehouse\Delivery;

class DeliveryUpdateForm extends DeliveryCreateForm
{
    public $delivery;

    /**
     * DeliveryUpdateForm constructor.
     * @param Delivery $delivery
     * @param array $config
     */
    public function __construct(Delivery $delivery, $config = [])
    {
        $this->delivery = $delivery;
        $this->contractor_id = $delivery->contractor_id;
        $this->division_id = $delivery->division_id;
        $this->invoice_number = $delivery->invoice_number;
        $this->delivery_date = $delivery->delivery_date;
        $this->notes = $delivery->notes;
        parent::__construct($config);
    }

}
