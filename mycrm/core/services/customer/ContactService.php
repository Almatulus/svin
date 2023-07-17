<?php

namespace core\services\customer;

use core\models\customer\Customer;
use core\models\customer\CustomerContact;

class ContactService
{
    /**
     * @param Customer $customer
     * @param Customer $contact
     * @return CustomerContact
     * @throws \DomainException
     */
    public function create(Customer $customer, Customer $contact): CustomerContact
    {
        $this->guardContact($customer->id, $contact->id);

        $contactCustomer = CustomerContact::find()->byCustomerAndContact($customer->id, $contact->id)->one();

        if (!$contactCustomer) {
            $contactCustomer = \Yii::createObject([
                'class'       => CustomerContact::class,
                'customer_id' => $customer->id,
                'contact_id'  => $contact->id
            ]);

            $contactCustomer->save(false);
        }

        return $contactCustomer;
    }

    /**
     * @param int $customer_id
     * @param $contact_id
     */
    private function guardContact(int $customer_id, $contact_id)
    {
        if ($customer_id == $contact_id) {
            throw new \DomainException('Customer cannot be in own contact list.');
        }
    }
}