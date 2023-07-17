<?php

namespace core\services\warehouse\dto;

class UsageDto
{
    /**
     * @var int
     */
    private $company_id;

    /**
     * @var int|null
     */
    private $company_customer_id;
    /**
     * @var int
     */
    private $discount;
    /**
     * @var int
     */
    private $division_id;
    /**
     * @var int|null
     */
    private $staff_id;
    /**
     * @var string|null
     */
    private $comments;

    /**
     * UsageDto constructor.
     * @param int $company_id
     * @param int $division_id
     * @param int $company_customer_id
     * @param int $staff_id
     * @param int $discount
     * @param string|null $comments
     */
    public function __construct(
        int $company_id,
        int $division_id,
        int $company_customer_id = null,
        int $staff_id = null,
        int $discount = 0,
        string $comments = null
    ) {
        $this->company_customer_id = $company_customer_id;
        $this->division_id = $division_id;
        $this->staff_id = $staff_id;
        $this->discount = $discount;
        $this->company_id = $company_id;
        $this->comments = $comments;
    }

    /**
     * @return int|null
     */
    public function getCompanyCustomerId()
    {
        return $this->company_customer_id;
    }

    /**
     * @return int
     */
    public function getDiscount(): int
    {
        return $this->discount;
    }

    /**
     * @return int
     */
    public function getDivisionId(): int
    {
        return $this->division_id;
    }

    /**
     * @return int|null
     */
    public function getStaffId()
    {
        return $this->staff_id;
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    /**
     * @return null|string
     */
    public function getComments()
    {
        return $this->comments;
    }
}