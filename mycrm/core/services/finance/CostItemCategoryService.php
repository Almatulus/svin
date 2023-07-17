<?php

namespace core\services\finance;

use core\models\finance\CompanyCostItem;
use core\models\finance\CompanyCostItemCategory;
use core\repositories\exceptions\NotFoundException;
use core\services\TransactionManager;

class CostItemCategoryService
{
    private $transactionManager;

    /**
     * CompanyCashService constructor.
     *
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        TransactionManager $transactionManager
    ) {
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param CompanyCostItemCategory $model
     *
     * @return CompanyCostItemCategory
     * @throws \Exception
     */
    public function create(CompanyCostItemCategory $model)
    {
        $this->transactionManager->execute(function () use ($model) {
            if (!$model->save()) {
                throw new \Exception(reset($model->getErrors())[0]);
            }
            CompanyCostItem::updateAll(['category_id' => $model->id], ['id' => $model->cost_items]);
        });

        return $model;
    }

    /**
     * @param CompanyCostItemCategory $model
     *
     * @return CompanyCostItemCategory
     * @throws \Exception
     */
    public function update(CompanyCostItemCategory $model)
    {
        $this->transactionManager->execute(function () use ($model) {
            if (!$model->save()) {
                throw new \Exception(reset($model->getErrors())[0]);
            }
            CompanyCostItem::updateAll(['category_id' => null], ['category_id' => $model->id]);
            CompanyCostItem::updateAll(['category_id' => $model->id], ['id' => $model->cost_items]);
        });

        return $model;
    }

    /**
     * @param $id
     *
     * @return CompanyCostItemCategory
     * @throws \Exception
     */
    public function delete($id)
    {
        $model = self::find($id);

        $this->transactionManager->execute(function () use ($model) {
            CompanyCostItem::updateAll(
                ['category_id' => null],
                ['category_id' => $model->id]
            );
            $model->delete();
        });

        return $model;
    }

    /**
     * @param $id
     *
     * @return CompanyCostItemCategory
     * @throws NotFoundException
     */
    public static function find($id)
    {
        if (!$model = CompanyCostItemCategory::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}
