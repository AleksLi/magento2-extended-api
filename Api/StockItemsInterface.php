<?php

namespace Noveo\WebApi\Api;

use Magento\Framework\DataObject;

/**
 * Interface StockItemsInterface
 * @api
 */
interface StockItemsInterface
{
    const KEY_SKU = 'sku';
    const KEY_QTY = 'qty';

    /**
     * @param string $productSKUs
     * @param int $scopeId
     * @return DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemsList($productSKUs, $scopeId = null);
}