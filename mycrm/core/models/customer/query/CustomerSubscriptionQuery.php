<?php

namespace core\models\customer\query;

use Yii;

/**
 * This is the ActiveQuery class for [[\core\models\customer\CustomerSubscription]].
 *
 * @see \core\models\customer\CustomerSubscription
 */
class CustomerSubscriptionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\customer\CustomerSubscription[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\customer\CustomerSubscription|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function company() 
    {
        return $this->joinWith('companyCustomer', false)->andWhere(['company_id' => Yii::$app->user->identity->company_id]);
    }
}
