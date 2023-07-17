<?php

namespace core\models\query;

use core\models\ServiceCategory;
use yii\db\ActiveQuery;

class ServiceCategoryQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return \core\models\ServiceCategory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\ServiceCategory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return ServiceCategoryQuery
     */
    public function root()
    {
        return $this->andWhere(['{{%service_categories}}.parent_category_id' => null]);
    }

    /**
     * @return ServiceCategoryQuery
     */
    public function enabled()
    {
        return $this->andWhere(['{{%service_categories}}.status' => ServiceCategory::STATUS_ENABLED]);
    }

    /**
     * @param $company_id
     * @return $this
     */
    public function byCompanyId(int $company_id = null)
    {
        if (!$company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->andWhere(['{{%service_categories}}.company_id' => $company_id]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byId($id)
    {
        return $this->andWhere(['{{%service_categories}}.id' => $id]);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function byName(string $name)
    {
        return $this->andWhere(['{{%service_categories}}.name' => $name]);
    }

    /**
     * @param int|null $parent_id
     * @return $this
     */
    public function parent(int $parent_id = null)
    {
        return $this->andWhere(['{{%service_categories}}.parent_category_id' => $parent_id]);
    }

    /**
     * @param array $parent_ids
     * @return $this
     */
    public function parents(array $parent_ids = null)
    {
        return $this->andWhere(['{{%service_categories}}.parent_category_id' => $parent_ids]);
    }

    /**
     * @return $this
     */
    public function notRoot()
    {
        return $this->andWhere('{{%service_categories}}.parent_category_id IS NOT NULL');
    }

    /**
     * @return ServiceCategoryQuery
     */
    public function staticType()
    {
        return $this->byType(ServiceCategory::TYPE_CATEGORY_STATIC);
    }

    /**
     * @return ServiceCategoryQuery
     */
    public function dynamicType()
    {
        return $this->byType(ServiceCategory::TYPE_CATEGORY_DYNAMIC);
    }

    /**
     * @param int $type
     * @return $this
     */
    public function byType(int $type)
    {
        return $this->andWhere(['{{%service_categories}}.type' => $type]);
    }

    /**
     * @param int $division_id
     * @param bool $eagerLoading
     * @return $this
     */
    public function byDivision($division_id, bool $eagerLoading = false)
    {
        return $this->joinWith('divisionServices.divisions',
            $eagerLoading)->andWhere(['{{%divisions}}.id' => $division_id]);
    }

    /**
     * @param array $division_ids
     * @param bool $eagerLoading
     * @return $this
     */
    public function byDivisions(array $division_ids, bool $eagerLoading = false)
    {
        return $this->joinWith('divisionServices.divisions',
            $eagerLoading)->andWhere(['{{%divisions}}.id' => $division_ids]);
    }

    /**
     * @param int|null $division_id
     * @param bool $eagerLoading
     * @return $this
     */
    public function filterByDivision($division_id = null, bool $eagerLoading = false)
    {
        return $this->joinWith('divisionServices.divisions',
            $eagerLoading)->andFilterWhere(['{{%divisions}}.id' => $division_id]);
    }
}
