<?php

namespace core\models\company\query;

/**
 * This is the ActiveQuery class for [[\core\models\company\TariffPayment]].
 *
 * @see \core\models\company\TariffPayment
 */
class TariffPaymentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\company\TariffPayment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\company\TariffPayment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
