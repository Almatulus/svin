<?php

namespace core\models\finance\query;

use yii\db\ActiveQuery;

class PayrollStaffQuery extends ActiveQuery
{
    /**
     * Filter by staff id
     * @param integer $staff_id
     * @return PayrollStaffQuery
     */
    public function staff($staff_id)
    {
        return $this->andWhere(['crm_staff_payrolls.staff_id' => $staff_id]);
    }
}