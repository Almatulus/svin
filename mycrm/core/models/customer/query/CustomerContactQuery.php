<?php

namespace core\models\customer\query;

use core\models\customer\CustomerContact;

/**
 * This is the ActiveQuery class for [[CustomerContact]].
 *
 * @see CustomerContact
 */
class CustomerContactQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return CustomerContact[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CustomerContact|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param int|null $company_id
     * @return $this
     */
    public function company(int $company_id = null)
    {
        if (!$company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        return $this->joinWith('customer.companyCustomers')
            ->andWhere(['{{%company_customers}}.company_id' => $company_id]);
    }

    /**
     * @param int $customer_id
     * @param int $contact_id
     * @return $this
     */
    public function byCustomerAndContact(int $customer_id, int $contact_id)
    {
        return $this->andWhere([
            "OR",
            [
                'AND',
                ['{{%customer_contacts}}.customer_id' => $customer_id],
                ['{{%customer_contacts}}.contact_id' => $contact_id],
            ],
            [
                'AND',
                ['{{%customer_contacts}}.contact_id' => $customer_id],
                ['{{%customer_contacts}}.customer_id' => $contact_id],
            ],
        ]);
    }
}
