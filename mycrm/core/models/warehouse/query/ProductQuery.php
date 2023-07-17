<?php

namespace core\models\warehouse\query;

use core\models\division\Division;
use core\models\warehouse\Product;
use core\models\warehouse\ProductType;

/**
 * This is the ActiveQuery class for [[\core\models\warehouse\Product]].
 *
 * @see \core\models\warehouse\Product
 */
class ProductQuery extends \yii\db\ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(["{{%warehouse_product}}.status" => Product::STATUS_ENABLED]);
    }

    /**
     * @param $status
     * @return $this
     */
    public function status($status)
    {
        return $this->andWhere(["{{%warehouse_product}}.status" => $status]);
    }

    /**
     * @return $this
     */
    public function division($division_id)
    {
        return $this->andWhere(["{{%warehouse_product}}.division_id" => $division_id]);
    }

    /**
     * @inheritdoc
     * @return \core\models\warehouse\Product[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\warehouse\Product|array|null
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

        $division_ids = Division::find()->select('id')->where(['company_id' => $company_id])->column();

        return $this->andWhere(['{{%warehouse_product}}.division_id' => $division_ids]);
    }

    /**
     * @return $this
     */
    public function permitted()
    {
        return $this->andFilterWhere(['{{%warehouse_product}}.division_id' => \Yii::$app->user->identity->permittedDivisions]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andFilterWhere(['{{%warehouse_product}}.id' => $id]);
    }

    /**
     * @param $type
     * @param bool $eagerLoading
     * @return $this
     */
    public function filterByType($type, bool $eagerLoading = false)
    {
        return $this->joinWith('productTypes', $eagerLoading)->andFilterWhere([
            ProductType::tableName() . '.id' => $type
        ]);
    }

    /**
     * @param $category_id
     * @return $this
     */
    public function filterByCategory($category_id)
    {
        return $this->andFilterWhere([Product::tableName() . '.category_id' => $category_id]);
    }

    /**
     * @return $this
     */
    public function withoutCategory()
    {
        return $this->andWhere(Product::tableName() . '.category_id IS NULL');
    }
}
