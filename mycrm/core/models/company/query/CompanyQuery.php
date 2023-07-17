<?php

namespace core\models\company\query;

use core\models\company\Company;
use yii\db\ActiveQuery;

class CompanyQuery extends ActiveQuery
{
    /**
     * Filter by enabled status
     *
     * @return CompanyQuery
     */
    public function enabled()
    {
        return $this->andWhere(['{{%companies}}.status' => Company::STATUS_ENABLED]);
    }

    /**
     * Filter by enabled integration
     *
     * @return CompanyQuery
     */
    public function enabledIntegration()
    {
        return $this->andWhere(['{{%companies}}.enable_integration' => true]);
    }
}
