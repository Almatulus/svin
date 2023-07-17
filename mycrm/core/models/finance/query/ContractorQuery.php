<?php

namespace core\models\finance\query;

/**
 * This is the ActiveQuery class for [[\core\models\finance\CompanyContractor]].
 *
 * @see \core\models\finance\CompanyContractor
 */
class ContractorQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\finance\CompanyContractor[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\finance\CompanyContractor|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Filter by division id
     * @param integer|null $division_id
     * @return ContractorQuery
     */
    public function division($division_id = null) {
        if ($division_id == null) {
            return $this->andWhere(['division_id' => \Yii::$app->user->identity->permittedDivisions]);
        }
        return $this->andWhere(['division_id' => $division_id]);
    }
}
