<?php

namespace common\components\parsers;

use core\forms\ImportForm;
use core\helpers\GenderHelper;
use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerCategory;
use DomainException;
use Exception;
use Yii;

class CustomerParser
{
    /**
     * @param ImportForm $model
     * @param Company    $company
     *
     * @return int
     * @throws Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \yii\db\Exception
     */
    public static function parse(ImportForm $model, Company $company)
    {
        $loadedFile = \PHPExcel_IOFactory::load($model->excelFile->tempName);
        $sheet = $loadedFile->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $savedCounter = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $dataRow = $sheet->rangeToArray('A' . $row . ':N' . $row, NULL, TRUE, FALSE);

            $customerData = self::getCustomerData($dataRow);
            if (empty($customerData['name'])) {
                continue;
            }

            $gender = self::getGender($dataRow[0][5]);
            $customerData['gender'] = $gender;
            $companyCustomerData = self::getCompanyCustomerData($dataRow);

            // Customer
            $customer = Customer::add(
                $customerData['phone'],
                $customerData['name'],
                $customerData['lastname'],
                $customerData['gender'],
                $customerData['birth_date'],
                $customerData['email']
            );

            // Company Customer
            $companyCustomer = CompanyCustomer::add(
                $customer,
                $company->id,
                $companyCustomerData['discount'],
                $companyCustomerData['sms_birthday'],
                $companyCustomerData['sms_exclude'],
                $companyCustomerData['comments'],
                null,
                $companyCustomerData['address'],
                $companyCustomerData['city']
            );

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ( ! $customer->insert()) {
                    $errors = $customer->errors;
                    throw new DomainException('Customer save error: '.reset($errors)[0]);
                }

                if ( ! $companyCustomer->insert()) {
                    $errors = $companyCustomer->errors;
                    throw new DomainException('Company Customer save error: '.reset($errors)[0]);
                }

                foreach ($companyCustomerData['categories'] as $category) {
                    /* @var CustomerCategory $category */
                    if ( ! $category->save()) {
                        $errors = $category->errors;
                        throw new DomainException('Customer category save error: '.reset($errors)[0]);
                    }
                    $companyCustomer->link('categories', $category);
                }

                $savedCounter++;
                $transaction->commit();
            } catch (DomainException $e) {
                $transaction->rollBack();
                throw $e;
            }

            unset($customer);
            unset($companyCustomer);
            unset($customerData);
            unset($companyCustomerData);
            unset($dataRow);
        }

        Yii::$app->session->set('progress', 100);

        return $savedCounter;
    }

    /**
     * @param $dataRow
     *
     * @return array
     */
    private static function getCustomerData($dataRow)
    {
        return [
            'phone'      => self::getPhone($dataRow[0][3]),
            'birth_date' => self::getDateTime($dataRow[0][6]),
            'email'      => self::getEmail($dataRow[0][9]),
            'lastname'   => trim($dataRow[0][2]),
            'name'       => trim($dataRow[0][1])
        ];
    }

    private static function getCompanyCustomerData($dataRow)
    {
        return [
            'discount'     => self::getDiscount($dataRow[0][4]),
            'city'         => trim($dataRow[0][7]),
            'address'      => trim($dataRow[0][8]),
            'comments'     => trim($dataRow[0][10]),
            'categories'   => self::getCategories($dataRow[0][11]),
            'sms_birthday' => self::getSmsOption($dataRow[0][12]),
            'sms_exclude'  => self::getSmsOption($dataRow[0][13])
        ];
    }

    private static function getGender($gender)
    {
        if ($gender == 'М') {
            return GenderHelper::GENDER_MALE;
        } else if ($gender == 'Ж') {
            return GenderHelper::GENDER_FEMALE;
        } else {
            return GenderHelper::GENDER_UNDEFINED;
        }
    }

    private static function getSmsOption($option)
    {
        if (strtolower($option) == 'да') {
            return true;
        }
        return false;
    }

    public static function getPhone($phone)
    {
        if (preg_match('/^\+(\d{1}) (\d{3}) (\d{3}) (\d{2}) (\d{2})$/', $phone, $matches)
            || preg_match('/^\+(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)
            || preg_match('/^(\d{1}) (\d{3}) (\d{3}) (\d{2}) (\d{2})$/', $phone, $matches)
            || preg_match('/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)
        ) {
            $phone = '+' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4] . ' ' . $matches[5];
            return $phone;
        }
        return null;
    }

    public static function getEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
    }

    public static function getCategories($customerCategories)
    {
        $categories = [];
        $defaultColor = '#000000';
        $customerCategories = explode(";", $customerCategories);
        foreach ($customerCategories as $categoryName) {
            $categoryName = trim($categoryName);

            if (empty($categoryName)) {
                continue;
            }

            $category = CustomerCategory::find()->where(['name' => $categoryName])->company()->one();
            if ($category) {
                $categories[] = $category;
            } else {
                $newCategory           = new CustomerCategory();
                $newCategory->name     = $categoryName;
                $newCategory->discount = 0;
                $newCategory->color    = $defaultColor;
                $newCategory->company_id
                    = Yii::$app->user->identity->company_id;
                $categories[] = $newCategory;
            }
        }
        return $categories;
    }

    public static function getDiscount($discount)
    {
        return filter_var(abs($discount), FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 0,
                'max_range' => 100
            )
        )) ?: 0;
    }

    public static function getDateTime($date)
    {
        $datetime = \DateTime::createFromFormat('m.d.Y', $date);
        return  $datetime ? $datetime->format('Y-m-d') : null;
    }
}