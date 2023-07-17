<?php

namespace core\repositories\customer;

use core\models\customer\CompanyCustomer;
use core\models\customer\CompanyCustomerPhone;
use core\repositories\exceptions\NotFoundException;

class CompanyCustomerRepository
{
    /**
     * @param $id
     * @return CompanyCustomer
     */
    public function find($id)
    {
        if (!$model = CompanyCustomer::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param CompanyCustomer $model
     */
    public function save(CompanyCustomer $model)
    {
        if ($model->save() === false) {
            throw new \RuntimeException(current($model->firstErrors));
        }
    }

    /**
     * @param integer $company_id
     * @return CompanyCustomer[]
     */
    public function findAllByCompanyHavingBirthdayToday($company_id)
    {
        return CompanyCustomer::find()
            ->joinWith(['customer customer'])
            ->andWhere(['company_id' => $company_id])
            ->andWhere(['sms_exclude' => false, 'sms_birthday' => true])
            ->andWhere("DATE_PART('day', customer.birth_date) = date_part('day', CURRENT_DATE)")
            ->andWhere("DATE_PART('month', customer.birth_date) = date_part('month', CURRENT_DATE)")
            ->all();
    }

    /**
     * @param $id
     * @param $category_id
     *
     * @throws \yii\db\Exception
     */
    public function linkCategory($id, $category_id)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->insert("{{%company_customer_category_map}}", [
            'company_customer_id' => $id,
            'category_id' => $category_id
        ])->execute();
    }

    /**
     * @param $id
     *
     * @throws \yii\db\Exception
     */
    public function unlinkAllCategories($id)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->delete("{{%company_customer_category_map}}", [
            'company_customer_id' => $id
        ])->execute();
    }

    /**
     * @param CompanyCustomer $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(CompanyCustomer $model)
    {
        if ($model->delete() === false) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param $phone
     * @param $company_id
     * @return array|CompanyCustomer|null|\yii\db\ActiveRecord
     */
    public function findByPhone($phone, $company_id)
    {
        return CompanyCustomer::find()->company($company_id)->phone($phone)->one();
    }

    /**
     * @param int $company_customer_id
     * @param $phone
     * @return null|static
     */
    public function findPhone(int $company_customer_id, $phone)
    {
        return CompanyCustomerPhone::findOne(['company_customer_id' => $company_customer_id, 'phone' => $phone]);
    }

    /**
     * @param $phone
     * @param $name
     * @param $lastName
     * @param $company_id
     * @return null|CompanyCustomer
     */
    public function findByPhoneAndName($phone, $name, $lastName, $company_id)
    {
        return CompanyCustomer::find()->company($company_id)
            ->phone($phone)
            ->andWhere(['c.name' => $name])
            ->andFilterWhere(['c.lastname' => $lastName])
            ->one();
    }
}
