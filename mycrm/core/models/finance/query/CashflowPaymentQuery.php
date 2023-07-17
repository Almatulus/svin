<?php

namespace core\models\finance\query;

/**
 * This is the ActiveQuery class for [[\core\models\finance\CashflowPayment]].
 *
 * @see \core\models\finance\CompanyCashflowPayment
 */
class CashflowPaymentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\finance\CompanyCashflowPayment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\finance\CompanyCashflowPayment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
