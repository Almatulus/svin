<?php

use yii\db\Migration;

/**
 * Class m180525_072401_fix_lor_praktika_customer_names
 */
class m180525_072401_fix_lor_praktika_customer_names extends Migration
{
    const LOR_PRAKTIKA = 211;
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $fixedCount = 0;
        $totalCount = 0;

        $query = \core\models\customer\CompanyCustomer::find()->company(self::LOR_PRAKTIKA);
        foreach ($query->each() as $companyCustomer){
            $customer = $companyCustomer->customer;
            $parts = explode(' ', $this->cleanName($customer->name));

            if(sizeof($parts) > 1 && $customer->lastname == ''){
                echo 'ID: ' . $companyCustomer->id . "\n";
                echo 'Name: ' . $customer->name . "\n";

                $customer->lastname = $parts[0] ?? '';
                $customer->name = $parts[1] ?? '';
                $customer->patronymic = $parts[2] ?? '';

                echo 'New lastname: ' . $customer->lastname. "\n";
                echo 'New name: ' . $customer->name . "\n";
                echo 'New patronymic: ' . $customer->patronymic . "\n"."\n";

                $customer->save();

                $fixedCount++;
            }
            $totalCount++;
        }

        echo "FINISHED, FIXED: {$fixedCount}/{$totalCount} ROWS \n\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /**
     * Replacing multiple spaces with a single space, and strip whitespace from the beginning and end of a string
     * @param $name
     * @return mixed
     */
    private function cleanName($name)
    {
        return preg_replace('!\s+!', ' ', trim($name));
    }
}
