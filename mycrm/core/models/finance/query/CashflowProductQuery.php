<?php

namespace core\models\finance\query;

/**
 * This is the ActiveQuery class for [[\core\models\finance\CashflowProduct]].
 *
 * @see \core\models\finance\CompanyCashflowProduct
 */
class CashflowProductQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\finance\CompanyCashflowProduct[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\finance\CompanyCashflowProduct|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
