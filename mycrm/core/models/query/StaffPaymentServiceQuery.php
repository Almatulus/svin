<?php

namespace core\models\query;

/**
 * This is the ActiveQuery class for [[\core\models\StaffPaymentService]].
 *
 * @see \core\models\StaffPaymentService
 */
class StaffPaymentServiceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\StaffPaymentService[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\StaffPaymentService|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
