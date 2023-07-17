<?php

namespace core\services\warehouse;

use core\models\warehouse\Delivery;
use core\models\warehouse\DeliveryProduct;
use core\repositories\warehouse\DeliveryProductRepository;
use core\repositories\warehouse\DeliveryRepository;
use core\services\TransactionManager;
use yii\helpers\ArrayHelper;

class DeliveryService
{
    private $deliveries;
    private $deliveryProducts;
    private $transactionManager;

    public function __construct(
        DeliveryRepository $deliveryRepository,
        DeliveryProductRepository $deliveryProductRepository,
        TransactionManager $transactionManager
    )
    {
        $this->deliveries = $deliveryRepository;
        $this->deliveryProducts = $deliveryProductRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param $company_id
     * @param $creator_id
     * @param $contractor_id
     * @param $division_id
     * @param $invoice_number
     * @param $delivery_date
     * @param $notes
     * @param $deliveryProducts DeliveryProduct[]
     * @return Delivery
     * @throws \Exception
     */
    public function create($company_id, $creator_id, $contractor_id, $division_id,
                           $invoice_number, $delivery_date, $notes, $deliveryProducts)
    {
        $delivery = Delivery::create(
            $company_id,
            $creator_id,
            $contractor_id,
            $division_id,
            $invoice_number,
            $delivery_date,
            $notes
        );

        $this->transactionManager->execute(function () use ($delivery, $deliveryProducts) {
            $this->deliveries->add($delivery);
            $this->insertProducts($delivery, $deliveryProducts);
        });

        return $delivery;
    }

    /**
     * @param $id
     * @param $contractor_id
     * @param $division_id
     * @param $invoice_number
     * @param $delivery_date
     * @param $notes
     * @param $deliveryProducts
     * @return Delivery
     * @throws \Exception
     */
    public function edit($id, $contractor_id, $division_id, $invoice_number,
                         $delivery_date, $notes, $deliveryProducts)
    {
        $delivery = $this->deliveries->find($id);

        $delivery->edit($contractor_id, $division_id, $invoice_number, $delivery_date, $notes);

        $oldIDs = ArrayHelper::map($delivery->products, 'id', 'id');
        $productsToDelete = array_diff($oldIDs, array_filter(ArrayHelper::map($deliveryProducts, 'id', 'id')));

        $this->transactionManager->execute(function () use ($delivery, $productsToDelete, $deliveryProducts) {
            $this->deliveries->edit($delivery);
            $this->clearProducts($productsToDelete);
            $this->insertProducts($delivery, $deliveryProducts);
        });

        return $delivery;
    }

    /**
     * @param Delivery $delivery
     * @param DeliveryProduct[] $deliveryProducts
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function insertProducts(Delivery $delivery, $deliveryProducts)
    {
        foreach ($deliveryProducts as $deliveryProduct) {
            $deliveryProduct->delivery_id = $delivery->id;
            if ($deliveryProduct->isNewRecord) {
                $deliveryProduct->product->addQuantity($deliveryProduct->quantity);
                $this->deliveryProducts->add($deliveryProduct);
                $this->deliveries->edit($deliveryProduct->product);
            } else {
                $oldQuantity = $deliveryProduct->getOldAttribute('quantity');
                $delta = $deliveryProduct->quantity - $oldQuantity;
                $deliveryProduct->product->addQuantity($delta);
                if ($deliveryProduct->product->quantity < 0) {
                    $deliveryProduct->product->quantity = 0;
                }
                $this->deliveryProducts->edit($deliveryProduct);
                $this->deliveries->edit($deliveryProduct->product);
            }
        }
    }

    /**
     * @param int[] $productsToDelete
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function clearProducts($productsToDelete)
    {
        foreach ($productsToDelete as $key => $product_id) {
            if($product_id !== null){
                $deliveryProduct = $this->deliveries->findProduct($product_id);
                $deliveryProduct->product->writeOff($deliveryProduct->quantity);
                $this->deliveryProducts->delete($deliveryProduct);
                $this->deliveries->edit($deliveryProduct->product);
            }
        }
    }

    /**
     * @param int $id
     */
    public function delete(int $id)
    {
        $model = $this->deliveries->find($id);

        if ($model->isDeleted()) {
            throw new \DomainException("Поставка уже удалена.");
        }

        $model->remove();

        $this->transactionManager->execute(function () use ($model) {
            $this->deliveries->edit($model);
            foreach ($model->products as $product) {
                if ($product->product->quantity < $product->quantity) {
                    throw new \DomainException("Количество на складе для товара '{$product->product->name}' меньше чем количество в поставке.");
                }
                $product->product->revertWriteOff(-$product->quantity);
                $this->deliveries->save($product->product);
            }
        });
    }
}
