<?php

namespace core\services\warehouse;

use core\models\warehouse\Product;
use core\models\warehouse\Stocktake;
use core\models\warehouse\StocktakeProduct;
use core\repositories\division\DivisionRepository;
use core\repositories\exceptions\InsufficientStockLevel;
use core\repositories\warehouse\ProductRepository;
use core\repositories\warehouse\StocktakeRepository;
use core\services\TransactionManager;
use yii\helpers\ArrayHelper;

class StocktakeService
{
    private $stocktakes;
    private $products;
    private $divisions;
    private $transactionManager;

    public function __construct(
        StocktakeRepository $stocktakeRepository,
        ProductRepository $productRepository,
        DivisionRepository $divisionRepository,
        TransactionManager $transactionManager
    )
    {
        $this->stocktakes = $stocktakeRepository;
        $this->products = $productRepository;
        $this->divisions = $divisionRepository;
        $this->transactionManager = $transactionManager;
    }

    public function getCurrent()
    {
        return $this->stocktakes->current();
    }

    public function create($type_of_products, $category_id, $name, $division_id, $creator_id, $description)
    {
        $this->guardCurrent();
        $this->guardProductExistence($division_id, $type_of_products, $category_id);

        $division = $this->divisions->find($division_id);

        $stocktake = Stocktake::create(
            $type_of_products,
            $category_id,
            $name,
            $division_id,
            $division->company_id,
            $creator_id,
            $description
        );

        $this->transactionManager->execute(function () use ($stocktake){
            $this->stocktakes->add($stocktake);
        });

        return $stocktake;
    }

    /**
     * @param $id
     * @param StocktakeProduct[] $stocktakeProducts
     * @return Stocktake
     */
    public function editProducts($id, $stocktakeProducts)
    {
        $stocktake = $this->stocktakes->find($id);

        $oldIDs = ArrayHelper::map($stocktake->products, 'id', 'id');
        $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($stocktakeProducts, 'id', 'id')));

        $this->transactionManager->execute(function () use ($stocktake, $stocktakeProducts, $deletedIDs){
            $this->stocktakes->deleteProducts($deletedIDs);

            foreach ($stocktakeProducts as $key => $stocktakeProduct) {
                $stocktakeProduct->stocktake_id = $stocktake->id;
                $stocktakeProduct->recorded_stock_level = $stocktakeProduct->product->quantity;
                $stocktakeProduct->purchase_price = $stocktakeProduct->product->purchase_price;

                $stocktakeProduct->save(false);
            }

            $stocktake->correct();
            $this->stocktakes->edit($stocktake);
        });

        return $stocktake;
    }

    /**
     * @param $id
     * @param StocktakeProduct[] $stocktakeProducts
     * @return Stocktake
     */
    public function updateProductsQuantity($id, $stocktakeProducts)
    {
        $stocktake = $this->stocktakes->find($id);

        $this->transactionManager->execute(function () use ($stocktake, $stocktakeProducts){
            foreach ($stocktakeProducts as $key => $stocktakeProduct) {
                if ($stocktakeProduct->apply_changes) {
                    $stocktakeProduct->product->quantity = $stocktakeProduct->actual_stock_level;
                    $this->products->edit($stocktakeProduct->product);
                }
                $this->stocktakes->save($stocktakeProduct);
            }

            $stocktake->complete();
            $this->stocktakes->edit($stocktake);
        });

        return $stocktake;
    }

    public function complete($id)
    {
        $stocktake = $this->stocktakes->find($id);
        $stocktake->complete();
        $this->stocktakes->edit($stocktake);

        return $stocktake;
    }

    /**
     * Get stocktake products, if empty find products
     * @param $id
     * @return StocktakeProduct[]
     */
    public function getProducts($id)
    {
        $stocktake = $this->stocktakes->find($id);
        $stocktakeProducts = $stocktake->products;

        if (empty($stocktakeProducts)) {
            $products = Product::find()
                ->company()
                ->division($stocktake->division_id)
                ->filterByType($stocktake->type_of_products)
                ->filterByCategory($stocktake->category_id)
                ->all();

            foreach ($products as $key => $product) {
                $stocktakeProducts[] = new StocktakeProduct([
                    'product_id'           => $product->id,
                    'recorded_stock_level' => $product->quantity,
                    'purchase_price'       => $product->purchase_price
                ]);
            }
        }

        return $stocktakeProducts;
    }

    private function guardCurrent()
    {
        if($this->stocktakes->current()){
            throw new \DomainException(\Yii::t('app',
                'It is necessary to complete the current stocktake!'));
        }
    }

    private function guardProductExistence($division_id, $type_of_products, $category_id)
    {
        $divisionHasProducts = $this->stocktakes->divisionHasProducts($division_id, $type_of_products, $category_id);
        if ( ! $divisionHasProducts) {
            throw new InsufficientStockLevel(\Yii::t('app', 'No products for stocktake'));
        }
    }
}
