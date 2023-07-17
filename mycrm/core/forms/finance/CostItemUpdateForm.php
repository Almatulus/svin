<?php

namespace core\forms\finance;

use core\models\finance\CompanyCostItem;
use Yii;

class CostItemUpdateForm extends CostItemForm
{
    public $costItem;

    /**
     * CostItemUpdateForm constructor.
     * @param CompanyCostItem $costItem
     * @param array $config
     */
    public function __construct(CompanyCostItem $costItem, $config = [])
    {
        $this->costItem = $costItem;
        $this->attributes = $costItem->attributes;
        $this->divisions = $costItem->getDivisions()->select('id')->column();
        parent::__construct($config);
    }

}
