<?php

namespace core\services\warehouse;

use core\models\warehouse\Usage;
use core\models\warehouse\UsageProduct;
use core\repositories\exceptions\InsufficientStockLevel;
use core\repositories\order\OrderRepository;
use core\repositories\warehouse\ProductRepository;
use core\repositories\warehouse\UsageRepository;
use core\services\TransactionManager;
use core\services\warehouse\dto\UsageDto;
use core\services\warehouse\dto\UsageProductDto;

class UsageService
{
    /** @var ProductRepository */
    protected $productRepository;
    /** @var OrderRepository */
    protected $orderRepository;
    /** @var UsageRepository */
    protected $usageRepository;
    /** @var TransactionManager */
    protected $transactionManager;

    /**
     * UsageService constructor.
     * @param UsageRepository $usageRepository
     * @param OrderRepository $orderRepository
     * @param ProductRepository $productRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        UsageRepository $usageRepository,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        TransactionManager $transactionManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->usageRepository = $usageRepository;
        $this->productRepository = $productRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param UsageDto $usageData
     * @param UsageProductDto[] $productsData
     * @return Usage
     */
    public function create(UsageDto $usageData, $productsData): Usage
    {
        $usage = Usage::add(
            $usageData->getCompanyId(),
            $usageData->getCompanyCustomerId(),
            $usageData->getDiscount(),
            $usageData->getDivisionId(),
            $usageData->getStaffId(),
            $usageData->getComments()
        );

        $products = [];
        foreach ($productsData as $productDto) {
            $products[] = $this->getProduct($productDto);
        }
        $usage->setUsageProducts($products);

        $this->transactionManager->execute(function () use ($usage) {
            $this->usageRepository->add($usage);

            foreach ($usage->usageProducts as $key => $product) {
                $product->usage_id = $usage->id;
                $this->usageRepository->add($product);
            }

            $this->writeOff($usage->usageProducts);
        });

        return $usage;
    }

    /**
     * @param int $id
     * @return Usage
     */
    public function cancel(int $id)
    {
        $usage = $this->usageRepository->find($id);
        $this->guardCancel($usage);

        $usage->cancel();

        $this->transactionManager->execute(function () use ($usage) {
            $this->usageRepository->edit($usage);
            $this->revertWriteOff($usage->usageProducts);
        });

        return $usage;
    }

    /**
     * @param int $id
     * @param UsageDto $usageData
     * @param UsageProductDto[] $productsData
     * @return Usage
     */
    public function update(int $id, UsageDto $usageData, $productsData): Usage
    {
        $usage = $this->usageRepository->find($id);
        $this->guardUpdate($usage);

        $usage->setAttributes([
            'company_id'          => $usageData->getCompanyId(),
            'company_customer_id' => $usageData->getCompanyCustomerId(),
            'division_id'         => $usageData->getDivisionId(),
            'discount'            => $usageData->getDiscount(),
            'staff_id'            => $usageData->getStaffId(),
            'comments'            => $usageData->getComments()
        ]);
        $usage->enable();

        $productsToSave = array_filter(array_map(function (UsageProductDto $productDto) {
            return $productDto->getId();
        }, $productsData));
        $productsToDelete = array_filter($usage->usageProducts,
            function (UsageProduct $usageProduct) use ($productsToSave) {
                return !in_array($usageProduct->id, $productsToSave);
            });

        $products = [];
        foreach ($productsData as $productDto) {
            $products[] = $this->getProduct($productDto);
        }
        $usage->setUsageProducts($products);

        $this->transactionManager->execute(function () use ($usage, $productsToDelete) {
            $this->usageRepository->edit($usage);

            foreach ($productsToDelete as $product) {
                $product->delete();
            }

            foreach ($usage->usageProducts as $product) {
                if ($product->isNewRecord) {
                    $product->usage_id = $usage->id;
                    $this->usageRepository->add($product);
                } else {
                    $this->usageRepository->edit($product);
                }
            }

            $this->writeOff($usage->usageProducts);
        });

        $usage->setUsageProducts($products);

        return $usage;
    }

    /**
     * @param int $id
     * @return Usage
     */
    public function delete(int $id)
    {
        $usage = $this->usageRepository->find($id);

        if (!$usage->isEnabled()) {
            throw new \DomainException("Данная операция невозможна.");
        }

        $usage->softDelete();

        $this->transactionManager->execute(function () use ($usage) {
            $this->usageRepository->edit($usage);
            $this->revertWriteOff($usage->usageProducts);
        });

        return $usage;
    }

    /**
     * @param UsageProductDto $usageProductDto
     * @return UsageProduct
     */
    public function getProduct(UsageProductDto $usageProductDto): UsageProduct
    {
        $product = $this->productRepository->find($usageProductDto->getProductId());
        if ($usageProductDto->getId()) {
            $usageProduct = $this->usageRepository->findProduct($usageProductDto->getId());
        } else {
            $usageProduct = new UsageProduct();
        }
        $usageProduct->setAttributes([
            'product_id'     => $usageProductDto->getProductId(),
            'quantity'       => $usageProductDto->getQuantity(),
            'selling_price'  => $usageProductDto->getSellingPrice() ?: $product->price,
            'purchase_price' => $usageProductDto->getPurchasePrice() ?: $product->purchase_price
        ]);

        return $usageProduct;
    }

    /**
     * @param UsageProduct[] $usageProducts
     * @param bool $ignoreInsufficientStockLevel
     */
    protected function writeOff(array $usageProducts, $ignoreInsufficientStockLevel = false)
    {
        foreach ($usageProducts as $key => $usageProduct) {

            if ($usageProduct->product->quantity < $usageProduct->quantity && !$ignoreInsufficientStockLevel) {
                throw new InsufficientStockLevel(\Yii::t('app',
                    'There are fewer items in stock than declared in the sale form'));
            }

            $usageProduct->product->writeOff($usageProduct->quantity);

            $this->usageRepository->edit($usageProduct->product);
        }
    }

    /**
     * @param UsageProduct[] $usageProducts
     */
    protected function revertWriteOff(array $usageProducts)
    {
        foreach ($usageProducts as $key => $usageProduct) {

            $usageProduct->product->revertWriteOff($usageProduct->quantity);

            $this->usageRepository->edit($usageProduct->product);
        }
    }

    /**
     * @param Usage $usage
     */
    private function guardUpdate(Usage $usage)
    {
        if ($usage->isEnabled()) {
            throw new \DomainException("Для редактирования необходимо сначала сделать возврат.");
        }
    }

    /**
     * @param Usage $usage
     */
    private function guardCancel(Usage $usage)
    {
        if ($usage->isCanceled()) {
            throw new \DomainException("На данное списание уже сделан возврат.");
        }
    }
}

