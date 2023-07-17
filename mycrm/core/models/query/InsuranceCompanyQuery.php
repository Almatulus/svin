<?php

namespace core\models\query;

/**
 * This is the ActiveQuery class for [[\core\models\InsuranceCompany]].
 *
 * @see \core\models\InsuranceCompany
 */
class InsuranceCompanyQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\InsuranceCompany[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\InsuranceCompany|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return $this
     */
    public function enabled()
    {
        $company_id = \Yii::$app->user->identity->company_id;

        return $this->joinWith('companyInsurances')->andWhere([
            '{{%company_insurances}}.company_id'   => $company_id,
            '{{%company_insurances}}.deleted_time' => null,
        ]);
    }

}
