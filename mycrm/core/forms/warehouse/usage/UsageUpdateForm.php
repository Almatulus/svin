<?php

namespace core\forms\warehouse\usage;

use core\models\warehouse\Usage;

/**
 * Class UsageUpdateForm
 * @package core\forms\warehouse\usage
 *
 * @property integer $company_customer_id
 * @property integer $discount
 * @property integer $division_id
 * @property integer $staff_id
 * @property string $updated_at
 * @property string $comments
 */
class UsageUpdateForm extends UsageCreateForm
{
    protected $usage;

    /**
     * UsageUpdateForm constructor.
     * @param int $id
     * @param array $config
     */
    public function __construct(int $id, array $config = [])
    {
        $this->usage = Usage::findOne($id);

        parent::__construct($config);
    }

    public function init()
    {
        if (!$this->usage) {
            throw new \InvalidArgumentException();
        }

        $this->setAttributes($this->usage->attributes);

        parent::init();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->usage->id;
    }
}