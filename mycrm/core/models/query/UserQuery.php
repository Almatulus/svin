<?php

namespace core\models\query;

use core\models\user\User;
use Yii;
use yii\db\ActiveQuery;

class UserQuery extends ActiveQuery
{
    /**
     * Filter by enabled staff
     * @return UserQuery
     */
    public function enabled()
    {
        return $this->andWhere(['{{%users}}.status' => User::STATUS_ENABLED]);
    }

    /**
     * Filter by disabled staff
     * @return UserQuery
     */
    public function disabled()
    {
        return $this->andWhere(['{{%users}}.status' => User::STATUS_DISABLED]);
    }

    /**
     * Filter by company
     * @param null $company_id
     * @return UserQuery
     */
    public function company($company_id = null)
    {
        if (!$company_id) { $company_id = Yii::$app->user->identity->company_id; }
        return $this->andWhere(['{{%users}}.company_id' => $company_id]);
    }

    /**
     * @param $role
     * @return $this
     */
    public function excludeRole($role)
    {
        return $this->leftJoin('{{%auth_assignment}}', '{{%auth_assignment}}.user_id::int = {{%users}}.id')
            ->andWhere([
                "OR",
                ['<>', '{{%auth_assignment}}.item_name', $role],
                '{{%auth_assignment}}.item_name IS NULL'
            ]);
    }
}