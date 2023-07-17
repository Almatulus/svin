<?php

namespace core\models\customer\query;

use yii\db\ActiveQuery;
use yii\db\Query;

class CustomerCategoryQuery extends ActiveQuery
{
    /**
     * Filter by company_id
     * @param integer $company_id
     * @return CustomerCategoryQuery
     */
    public function company($company_id = null)
    {
        if ($company_id == null) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->andWhere(['{{%customer_categories}}.company_id' => $company_id]);
    }
}
