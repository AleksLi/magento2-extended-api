<?php

namespace Noveo\WebApi\Model;

use Magento\Catalog\Model\Product\Website;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\DataObject;
use Noveo\WebApi\Api\StockItemsInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class StockItems implements StockItemsInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var DataObject
     */
    private $resultItems;

    /**
     * @var Website
     */
    private $productWebsite;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StockRegistryInterface $stockRegistry
     * @param DataObject $resultItems
     * @param Website $productWebsite
     * @param ProductFactory $productFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        DataObject $resultItems,
        Website $productWebsite,
        ProductFactory $productFactory
    ) {
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->resultItems = $resultItems;
        $this->productWebsite = $productWebsite;
        $this->productFactory = $productFactory;
    }

    /**
     * Get list of items quantity using their SKUs
     *
     * @param string $productSKUs
     * @param null $storeId
     * @return array
     */
    public function getStockItemsList($productSKUs, $storeId = null)
    {
        $storeId = $storeId ?? $this->storeManager->getStore()->getId();
        $handledSKUs = explode(',', $productSKUs);

        foreach ($handledSKUs as $productSKU) {
            $item = [];
            /** @var \Magento\Catalog\Model\Product  $product */
            $product = $this->productFactory->create();
            $productId = $product->getIdBySku($productSKU);
            $productWebsites = $this->productWebsite->getWebsites($productId);

            $item[static::KEY_SKU] = $productSKU;

            if (!$productWebsites || !in_array($storeId,$productWebsites[$productId])) {
                $this->setEmptyQty($productSKU, $item);
                continue;
            }
            $stockItem = $this->stockRegistry->getStockItem($productId, $storeId);
            $item[static::KEY_QTY] = $stockItem->getQty();
            $this->getResultItems()->setData($productSKU, $item);
        }
        return $this->getResultItems()->getData();
    }

    /**
     * @param string $sku
     * @param array $item
     */
    protected function setEmptyQty($sku, $item)
    {
        $item[static::KEY_QTY] = 0;
        $this->getResultItems()->setData($sku, $item);
    }

    /**
     * @return DataObject
     */
    public function getResultItems()
    {
        return $this->resultItems;
    }
}