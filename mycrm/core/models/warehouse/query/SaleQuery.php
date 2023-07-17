<?php

namespace core\models\warehouse\query;

use core\models\division\Division;
use core\models\warehouse\Sale;
use yii\db\ActiveQuery;

class SaleQuery extends ActiveQuery
{
    /**
     * Filter by company
     * @param int $company_id
     * @return SaleQuery
     */
    public function company($company_id = null)
    {
        if ( ! $company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        $division_ids = Division::find()->select('id')->where(['company_id' => $company_id])->column();

        return $this->andWhere([Sale::tableName() . '.division_id' => $division_ids]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere([Sale::tableName() . '.id' => $id]);
    }
}