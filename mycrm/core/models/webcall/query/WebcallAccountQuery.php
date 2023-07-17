<?php

namespace core\models\webcall\query;

/**
 * This is the ActiveQuery class for [[\core\models\webcall\WebcallAccount]].
 *
 * @see \core\models\webcall\WebcallAccount
 */
class WebcallAccountQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\webcall\WebcallAccount[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\webcall\WebcallAccount|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $company_id
     * @param bool $eagerLoading
     * @return $this
     */
    public function company($company_id = null, $eagerLoading = false)
    {
        if (!$company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        return $this->joinWith('division', $eagerLoading)->andWhere([
            '{{%divisions}}.company_id' => $company_id
        ]);
    }

    /**
     * @param int $division_id
     * @return $this
     */
    public function division(int $division_id)
    {
        return $this->andWhere(['{{%company_webcall_accounts}}.division_id' => $division_id]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere(['{{%company_webcall_accounts}}.id' => $id]);
    }
}
