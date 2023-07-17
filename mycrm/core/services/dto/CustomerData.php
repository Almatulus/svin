<?php

namespace core\services\dto;

/**
 * @property integer $company_customer_id
 * @property string  $name
 * @property string  $surname
 * @property string  $patronymic
 * @property string  $phone
 * @property integer $source_id
 * @property integer $medical_record_id
 * @property string $birth_date
 * @property integer gender
 * @property integer $insurance_company_id
 * @property array $categories
 */
class CustomerData
{
    public $name;
    public $surname;
    public $patronymic;
    public $phone;
    public $source_id;
    public $company_customer_id;
    public $medical_record_id;
    public $birth_date;
    public $gender;
    public $insurance_company_id;
    public $categories;

    public function __construct(
        $company_customer_id,
        $name,
        $surname,
        $patronymic,
        $phone,
        $source_id,
        $medical_record_id = null,
        $birth_date = null,
        $gender = null,
        $insurance_company_id = null,
        $categories = null
    ) {
        $this->name                = $name;
        $this->surname             = $surname;
        $this->phone               = $phone;
        $this->source_id           = $source_id;
        $this->company_customer_id = $company_customer_id;
        $this->patronymic          = $patronymic;
        $this->medical_record_id = $medical_record_id;
        $this->birth_date = $birth_date;
        $this->gender = $gender;
        $this->insurance_company_id = $insurance_company_id;
        $this->categories = $categories;
    }
}