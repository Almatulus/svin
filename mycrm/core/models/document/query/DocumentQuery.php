<?php

namespace core\models\document\query;

/**
 * This is the ActiveQuery class for [[\core\models\document\Document]].
 *
 * @see \core\models\document\Document
 */
class DocumentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\document\Document[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\document\Document|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param null $company_id
     * @return $this
     */
    public function company($company_id = null)
    {
        if (!$company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->joinWith('companyCustomer')->andWhere(['{{%company_customers}}.company_id' => $company_id]);
    }
}
