<?php

namespace core\models\company\query;

use yii\db\ActiveQuery;

class CompanyPositionQuery extends ActiveQuery
{
    /**
     * Filter not deleted records
     *
     * @return self
     */
    public function notDeleted()
    {
        return $this->andWhere(['{{%company_positions}}.deleted_time' => null]);
    }

    /**
     * Filter by company_id
     *
     * @param integer $company_id
     *
     * @return self
     */
    public function company($company_id)
    {
        return $this->andWhere(['{{%company_positions}}.company_id' => $company_id]);
    }

    /**
     * Filter by ID
     *
     * @param $id
     *
     * @return self
     */
    public function position($id)
    {
        return $this->andFilterWhere(['{{%company_positions}}.id' => $id]);
    }
}
